<?php

namespace Tests\Feature\Navigation;

use Tests\TestCase;
use Tests\FakesRoles;
use Statamic\Facades\Nav;
use Statamic\Facades\User;
use Tests\PreventSavingStacheItemsToDisk;

class DeleteNavigationTest extends TestCase
{
    use FakesRoles;
    use PreventSavingStacheItemsToDisk;

    /** @test */
    function it_denies_access_if_you_dont_have_permission()
    {
        $this->setTestRoles(['test' => ['access cp']]);
        $user = tap(User::make()->assignRole('test'))->save();

        $nav = $this->createNav();
        $this->assertCount(1, Nav::all());

        $this
            ->from('/original')
            ->actingAs($user)
            ->delete(cp_route('navigation.destroy', $nav->handle()))
            ->assertRedirect('/original')
            ->assertSessionHas('error', 'You are not authorized to configure navs.');

        $this->assertCount(1, Nav::all());
    }

    /** @test */
    function it_deletes_the_navigation()
    {
        $this->setTestRoles(['test' => ['access cp', 'configure navs']]);
        $user = tap(User::make()->assignRole('test'))->save();

        $nav = $this->createNav();
        $this->assertCount(1, Nav::all());

        $this
            ->actingAs($user)
            ->delete(cp_route('navigation.destroy', $nav->handle()))
            ->assertOk();

        $this->assertCount(0, Nav::all());
    }

    private function createNav()
    {
        return Nav::make('test')
            ->title('Existing')
            ->maxDepth(1)
            ->expectsRoot(false)
            ->tap(function ($nav) {
                $nav->addTree($nav->makeTree('en'));
                $nav->save();
            });
    }
}
