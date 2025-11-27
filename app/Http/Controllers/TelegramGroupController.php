<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramGroupController extends Controller
{
    private $botToken;
    private $apiUrl;
    private $botUserId;

    public function __construct()
    {
        $this->botToken = env('TELEGRAM_BOT_TOKEN');
        $this->apiUrl = "https://api.telegram.org/bot{$this->botToken}";
        $this->botUserId = env('TELEGRAM_BOT_OWNER_ID'); // The bot owner's Telegram user ID
    }

    /**
     * Show the form for creating a new group
     */
    public function showForm()
    {
        return inertia('Telegram/CreateGroup');
    }

    /**
     * Handle the form submission and create the Telegram group
     */
    public function createGroup(Request $request)
    {
        // Validate the incoming request
        $validated = $request->validate([
            'group_name' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'admins' => 'required|array|min:1',
            'admins.*.user_id' => 'required|string',
            'admins.*.name' => 'nullable|string',
            'permissions' => 'nullable|array',
        ]);

        try {
            // Step 1: Create the group
            $groupResponse = $this->createTelegramGroup($validated['group_name']);

            if (!$groupResponse['ok']) {
                throw new \Exception('Failed to create group: ' . ($groupResponse['description'] ?? 'Unknown error'));
            }

            $chatId = $groupResponse['result']['id'];
            $groupTitle = $groupResponse['result']['title'];

            // Step 2: Set group description if provided
            if (!empty($validated['description'])) {
                $this->setGroupDescription($chatId, $validated['description']);
            }

            // Step 3: Prepare permissions
            $permissions = $this->preparePermissions($request->input('permissions', []));

            // Step 4: Add admins to the group
            $adminResults = [];
            foreach ($validated['admins'] as $admin) {
                $result = $this->addAdminToGroup($chatId, $admin['user_id'], $permissions);
                $adminResults[] = [
                    'user_id' => $admin['user_id'],
                    'name' => $admin['name'] ?? 'Unknown',
                    'success' => $result['ok'] ?? false,
                    'error' => $result['description'] ?? null
                ];
            }

            // Step 5: Send notification to bot owner
            $this->sendNotificationToOwner($groupTitle, $chatId, $adminResults);

            // Step 6: Get invite link
            $inviteLink = $this->getGroupInviteLink($chatId);

            // Log the successful creation
            Log::info('Telegram group created', [
                'group_name' => $groupTitle,
                'chat_id' => $chatId,
                'admins' => $adminResults
            ]);

            return redirect()->back()->with('success',
                "Group '{$groupTitle}' created successfully! Chat ID: {$chatId}" .
                ($inviteLink ? " | Invite Link: {$inviteLink}" : "")
            );

        } catch (\Exception $e) {
            Log::error('Failed to create Telegram group', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to create group: ' . $e->getMessage());
        }
    }

    /**
     * Create a Telegram group using the Bot API
     */
    private function createTelegramGroup($title)
    {
        $response = Http::post("{$this->apiUrl}/createGroup", [
            'title' => $title,
        ]);

        return $response->json();
    }

    /**
     * Set group description
     */
    private function setGroupDescription($chatId, $description)
    {
        $response = Http::post("{$this->apiUrl}/setChatDescription", [
            'chat_id' => $chatId,
            'description' => $description,
        ]);

        return $response->json();
    }

    /**
     * Add an admin to the group with specified permissions
     */
    private function addAdminToGroup($chatId, $userId, $permissions)
    {
        // First, try to add the user to the group
        $addResponse = Http::post("{$this->apiUrl}/addChatMember", [
            'chat_id' => $chatId,
            'user_id' => $this->resolveUserId($userId),
        ]);

        // Then promote them to admin with permissions
        $promoteResponse = Http::post("{$this->apiUrl}/promoteChatMember", array_merge([
            'chat_id' => $chatId,
            'user_id' => $this->resolveUserId($userId),
        ], $permissions));

        return $promoteResponse->json();
    }

    /**
     * Resolve username to user ID (if username is provided)
     * Note: This is a simplified version. Username resolution requires the user
     * to have interacted with the bot or be in a common group.
     */
    private function resolveUserId($userIdOrUsername)
    {
        // If it starts with @, remove it
        if (strpos($userIdOrUsername, '@') === 0) {
            return substr($userIdOrUsername, 1);
        }
        return $userIdOrUsername;
    }

    /**
     * Prepare permissions array from checkbox inputs
     */
    private function preparePermissions($permissionsInput)
    {
        return [
            'can_change_info' => isset($permissionsInput['can_change_info']),
            'can_delete_messages' => isset($permissionsInput['can_delete_messages']),
            'can_invite_users' => isset($permissionsInput['can_invite_users']),
            'can_restrict_members' => isset($permissionsInput['can_restrict_members']),
            'can_pin_messages' => isset($permissionsInput['can_pin_messages']),
            'can_promote_members' => isset($permissionsInput['can_promote_members']),
        ];
    }

    /**
     * Send a notification message to the bot owner
     */
    private function sendNotificationToOwner($groupName, $chatId, $adminResults)
    {
        if (!$this->botUserId) {
            Log::warning('TELEGRAM_BOT_OWNER_ID not set in .env file');
            return;
        }

        $adminsList = '';
        foreach ($adminResults as $admin) {
            $status = $admin['success'] ? '✅' : '❌';
            $adminsList .= "\n  {$status} {$admin['name']} ({$admin['user_id']})";
            if (!$admin['success'] && $admin['error']) {
                $adminsList .= "\n     Error: {$admin['error']}";
            }
        }

        $message = "🎉 <b>New Telegram Group Created!</b>\n\n";
        $message .= "📝 <b>Group Name:</b> {$groupName}\n";
        $message .= "🆔 <b>Chat ID:</b> <code>{$chatId}</code>\n";
        $message .= "👥 <b>Admins Added:</b>{$adminsList}\n\n";
        $message .= "✨ The group has been successfully created and configured!";

        Http::post("{$this->apiUrl}/sendMessage", [
            'chat_id' => $this->botUserId,
            'text' => $message,
            'parse_mode' => 'HTML',
        ]);
    }

    /**
     * Get group invite link
     */
    private function getGroupInviteLink($chatId)
    {
        $response = Http::post("{$this->apiUrl}/exportChatInviteLink", [
            'chat_id' => $chatId,
        ]);

        $result = $response->json();
        return $result['ok'] ? $result['result'] : null;
    }
}
