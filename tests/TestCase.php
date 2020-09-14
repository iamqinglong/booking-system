<?php

namespace Tests;

use App\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;
    use DatabaseMigrations;
    use RefreshDatabase;

    public function signIn($user = null)
    {
        $user = $user ?: factory(User::class)->create();

        $user->assignRole('User');

        $this->actingAs($user);

        return $this;
    }

    public function signInUserRoleApi($user = null)
    {
        $user = $user ?: factory(User::class)->create();

        $user->assignRole('User');

        $this->actingAs($user, 'api');

        return $this;
    }

    public function signInAdmin($user = null)
    {
        $user = factory(User::class)->create();

        $user->assignRole('Admin');

        $this->actingAs($user);

        return $this;
    }
}
