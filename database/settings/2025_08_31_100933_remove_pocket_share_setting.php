<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration {
    public function up(): void
    {
        if ($this->migrator->exists('guest.share_pocket')) {
            $this->migrator->delete('guest.share_pocket');
        }

        foreach (DB::table('users')->pluck('id') as $userId) {
            $id = 'user-' . $userId;
            if ($this->migrator->exists($id . '.share_pocket')) {
                $this->migrator->delete($id . '.share_pocket');
            }
        }
    }
};
