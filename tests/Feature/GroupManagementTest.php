<?php

namespace Tests\Feature;

use App\Http\Livewire\Groups\GroupDetail;
use App\Models\Group;
use App\Models\GroupMember;
use App\Models\GroupMessage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class GroupManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_group_creator_can_update_settings(): void
    {
        $creator = User::factory()->create(['username' => 'u_' . uniqid()]);
        $group = Group::create([
            'name'       => 'Test Guruh',
            'slug'       => 'test-guruh-' . uniqid(),
            'created_by' => $creator->id,
        ]);

        GroupMember::create([
            'group_id' => $group->id,
            'user_id'  => $creator->id,
            'role'     => 'admin',
        ]);

        Livewire::actingAs($creator)
            ->test(GroupDetail::class, ['group' => $group])
            ->call('openSettingsModal')
            ->set('editName', 'Yangilangan Guruh')
            ->set('editChatEnabled', false)
            ->set('editVoiceEnabled', false)
            ->call('saveSettings')
            ->assertHasNoErrors();

        $group->refresh();
        $this->assertEquals('Yangilangan Guruh', $group->name);
        $this->assertFalse($group->chat_enabled);
        $this->assertFalse($group->voice_enabled);
    }

    public function test_regular_member_cannot_post_when_chat_is_disabled(): void
    {
        $creator = User::factory()->create(['username' => 'u_' . uniqid()]);
        $member = User::factory()->create(['username' => 'u_' . uniqid()]);

        $group = Group::create([
            'name'         => 'Quiet Group',
            'slug'         => 'quiet-group-' . uniqid(),
            'created_by'   => $creator->id,
            'chat_enabled' => false,
        ]);

        GroupMember::create(['group_id' => $group->id, 'user_id' => $creator->id, 'role' => 'admin']);
        GroupMember::create(['group_id' => $group->id, 'user_id' => $member->id, 'role' => 'member']);

        // Member attempts to send message
        Livewire::actingAs($member)
            ->test(GroupDetail::class, ['group' => $group])
            ->set('message', 'Salom hammaga')
            ->call('sendMessage')
            ->assertHasErrors(['message']);

        $this->assertDatabaseMissing('group_messages', [
            'group_id' => $group->id,
            'user_id'  => $member->id,
        ]);

        // Creator CAN send message even if chat is disabled for members
        Livewire::actingAs($creator)
            ->test(GroupDetail::class, ['group' => $group])
            ->set('message', 'E\'lon: hurmatli a\'zolar...')
            ->call('sendMessage')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('group_messages', [
            'group_id' => $group->id,
            'user_id'  => $creator->id,
            'message'  => 'E\'lon: hurmatli a\'zolar...',
        ]);
    }

    public function test_regular_member_cannot_send_voice_when_voice_is_disabled(): void
    {
        $creator = User::factory()->create(['username' => 'u_' . uniqid()]);
        $member = User::factory()->create(['username' => 'u_' . uniqid()]);

        $group = Group::create([
            'name'          => 'No Voice Group',
            'slug'          => 'no-voice-' . uniqid(),
            'created_by'    => $creator->id,
            'chat_enabled'  => true,
            'voice_enabled' => false,
        ]);

        GroupMember::create(['group_id' => $group->id, 'user_id' => $creator->id, 'role' => 'admin']);
        GroupMember::create(['group_id' => $group->id, 'user_id' => $member->id, 'role' => 'member']);

        Livewire::actingAs($member)
            ->test(GroupDetail::class, ['group' => $group])
            ->call('sendVoiceMessage', 'voices/sample.webm', 15)
            ->assertHasErrors(['message']);

        $this->assertDatabaseMissing('group_messages', [
            'group_id' => $group->id,
            'user_id'  => $member->id,
        ]);
    }

    public function test_group_creator_can_kick_member(): void
    {
        $creator = User::factory()->create(['username' => 'u_' . uniqid()]);
        $member = User::factory()->create(['username' => 'u_' . uniqid()]);

        $group = Group::create([
            'name'       => 'Test Kick Group',
            'slug'       => 'test-kick-' . uniqid(),
            'created_by' => $creator->id,
        ]);

        GroupMember::create(['group_id' => $group->id, 'user_id' => $creator->id, 'role' => 'admin']);
        GroupMember::create(['group_id' => $group->id, 'user_id' => $member->id, 'role' => 'member']);

        Livewire::actingAs($creator)
            ->test(GroupDetail::class, ['group' => $group])
            ->call('openRemoveMemberModal', $member->id)
            ->call('confirmRemoveMember');

        $this->assertDatabaseMissing('group_members', [
            'group_id' => $group->id,
            'user_id'  => $member->id,
        ]);
    }

    public function test_member_can_leave_group(): void
    {
        $creator = User::factory()->create(['username' => 'u_' . uniqid()]);
        $member = User::factory()->create(['username' => 'u_' . uniqid()]);

        $group = Group::create([
            'name'       => 'Test Leave Group',
            'slug'       => 'test-leave-' . uniqid(),
            'created_by' => $creator->id,
        ]);

        GroupMember::create(['group_id' => $group->id, 'user_id' => $creator->id, 'role' => 'admin']);
        GroupMember::create(['group_id' => $group->id, 'user_id' => $member->id, 'role' => 'member']);

        Livewire::actingAs($member)
            ->test(GroupDetail::class, ['group' => $group])
            ->call('leaveGroup')
            ->assertRedirect(route('groups.index'));

        $this->assertDatabaseMissing('group_members', [
            'group_id' => $group->id,
            'user_id'  => $member->id,
        ]);
    }

    public function test_group_creator_can_toggle_moderator(): void
    {
        $creator = User::factory()->create(['username' => 'u_' . uniqid()]);
        $member = User::factory()->create(['username' => 'u_' . uniqid()]);

        $group = Group::create([
            'name'       => 'Mod Test Group',
            'slug'       => 'mod-test-' . uniqid(),
            'created_by' => $creator->id,
        ]);

        GroupMember::create(['group_id' => $group->id, 'user_id' => $creator->id, 'role' => 'admin']);
        $gm = GroupMember::create(['group_id' => $group->id, 'user_id' => $member->id, 'role' => 'member']);

        Livewire::actingAs($creator)
            ->test(GroupDetail::class, ['group' => $group])
            ->call('toggleModerator', $member->id);

        $gm->refresh();
        $this->assertEquals('moderator', $gm->role);

        // Toggle back to member
        Livewire::actingAs($creator)
            ->test(GroupDetail::class, ['group' => $group])
            ->call('toggleModerator', $member->id);

        $gm->refresh();
        $this->assertEquals('member', $gm->role);
    }

    public function test_non_creator_cannot_kick_or_update_settings(): void
    {
        $creator = User::factory()->create(['username' => 'u_' . uniqid()]);
        $member1 = User::factory()->create(['username' => 'u_' . uniqid()]);
        $member2 = User::factory()->create(['username' => 'u_' . uniqid()]);

        $group = Group::create([
            'name'       => 'Secure Group',
            'slug'       => 'sec-test-' . uniqid(),
            'created_by' => $creator->id,
        ]);

        GroupMember::create(['group_id' => $group->id, 'user_id' => $creator->id, 'role' => 'admin']);
        GroupMember::create(['group_id' => $group->id, 'user_id' => $member1->id, 'role' => 'member']);
        GroupMember::create(['group_id' => $group->id, 'user_id' => $member2->id, 'role' => 'member']);

        // member1 tries to kick member2
        Livewire::actingAs($member1)
            ->test(GroupDetail::class, ['group' => $group])
            ->call('openRemoveMemberModal', $member2->id);

        $this->assertDatabaseHas('group_members', [
            'group_id' => $group->id,
            'user_id'  => $member2->id,
        ]);

        // member1 tries to save settings
        Livewire::actingAs($member1)
            ->test(GroupDetail::class, ['group' => $group])
            ->set('editName', 'Hacked Name')
            ->call('saveSettings');

        $group->refresh();
        $this->assertEquals('Secure Group', $group->name);
    }

    public function test_regular_member_can_send_voice_when_chat_is_disabled_but_voice_is_enabled(): void
    {
        $creator = User::factory()->create(['username' => 'u_' . uniqid()]);
        $member = User::factory()->create(['username' => 'u_' . uniqid()]);

        $group = Group::create([
            'name'          => 'Voice Only Group',
            'slug'          => 'voice-only-' . uniqid(),
            'created_by'    => $creator->id,
            'chat_enabled'  => false,
            'voice_enabled' => true,
        ]);

        GroupMember::create(['group_id' => $group->id, 'user_id' => $creator->id, 'role' => 'admin']);
        GroupMember::create(['group_id' => $group->id, 'user_id' => $member->id, 'role' => 'member']);

        // Member attempts to send text message -> fails
        Livewire::actingAs($member)
            ->test(GroupDetail::class, ['group' => $group])
            ->set('message', 'Matnli xabar')
            ->call('sendMessage')
            ->assertHasErrors(['message']);

        // Member attempts to send voice message -> succeeds!
        Livewire::actingAs($member)
            ->test(GroupDetail::class, ['group' => $group])
            ->call('sendVoiceMessage', 'voices/voice_test.webm', 20)
            ->assertHasNoErrors();

        $this->assertDatabaseHas('group_messages', [
            'group_id'   => $group->id,
            'user_id'    => $member->id,
            'audio_path' => 'voices/voice_test.webm',
        ]);
    }

    public function test_group_creator_can_upload_and_remove_cover_image(): void
    {
        \Illuminate\Support\Facades\Storage::fake('public');

        $creator = User::factory()->create(['username' => 'u_' . uniqid()]);
        $group = Group::create([
            'name'       => 'Image Test Group',
            'slug'       => 'img-group-' . uniqid(),
            'created_by' => $creator->id,
        ]);
        GroupMember::create(['group_id' => $group->id, 'user_id' => $creator->id, 'role' => 'admin']);

        $file = \Illuminate\Http\UploadedFile::fake()->image('cover.jpg');

        Livewire::actingAs($creator)
            ->test(GroupDetail::class, ['group' => $group])
            ->set('newCoverImage', $file)
            ->call('updateCoverImage')
            ->assertHasNoErrors();

        $group->refresh();
        $this->assertNotNull($group->cover_image);
        \Illuminate\Support\Facades\Storage::disk('public')->assertExists($group->cover_image);

        // Remove cover image
        Livewire::actingAs($creator)
            ->test(GroupDetail::class, ['group' => $group])
            ->call('removeCoverImage')
            ->assertHasNoErrors();

        $group->refresh();
        $this->assertNull($group->cover_image);
    }
}
