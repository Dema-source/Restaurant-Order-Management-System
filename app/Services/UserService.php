<?php

namespace App\Services;

use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class UserService extends BaseService
{
    public function __construct(UserRepositoryInterface $repository)
    {
        parent::__construct($repository);
    }

    public function create(array $data): Model
    {
        $roles = $data['roles'] ?? [];
        unset($data['roles']);

        // Auto-generate email from name
        $data['email'] = $this->generateEmailFromName($data['name']);

        $user = parent::create($data);

        if (!empty($roles)) {
            $user->syncRoles($roles);
        }

        return $user;
    }

    public function update(int $id, array $data): bool
    {
        $roles = $data['roles'] ?? null;
        unset($data['roles']);

        if (isset($data['password']) && empty($data['password'])) {
            unset($data['password']);
        }

        $updated = parent::update($id, $data);

        if ($updated && $roles !== null) {
            $user = $this->findById($id);
            if ($user) {
                $user->syncRoles($roles);
            }
        }

        return $updated;
    }

    public function changePassword(int $userId, string $newPassword, ?string $oldPassword = null): void
    {
        $user = $this->findById($userId);

        if (!$user) {
            throw ValidationException::withMessages([
                'user' => ['User not found.'],
            ]);
        }

        if ($oldPassword && !Hash::check($oldPassword, $user->password)) {
            throw ValidationException::withMessages([
                'old_password' => ['The current password is incorrect.'],
            ]);
        }

        $user->password = Hash::make($newPassword);
        $user->save();
    }

    public function me(mixed $user): mixed
    {
        return $user;
    }
   
    private function generateEmailFromName(string $name): string
    {
        // Convert name to lowercase and replace spaces with dots
        $emailName = strtolower(str_replace(' ', '.', trim($name)));
        
        // Remove special characters
        $emailName = preg_replace('/[^a-z0-9.]/', '', $emailName);
        
        // Get domain from config or use default
        $domain = config('app.user_email_domain', 'restaurant.com');
        
        // Ensure uniqueness
        $baseEmail = $emailName . '@' . $domain;
        $email = $baseEmail;
        $counter = 1;
        
        while (\App\Models\User::where('email', $email)->exists()) {
            $email = $emailName . $counter . '@' . $domain;
            $counter++;
        }
        
        return $email;
    }
}
