<?php

namespace Tests;

use App\Models\User;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

public function actingAs(Authenticatable $user, $guard = null)
    {
        if ($user instanceof User && $user->isAdmin()) {
            $this->withSession(['admin_pin_verified' => true]);
        }

        return parent::actingAs($user, $guard);
    }
}
