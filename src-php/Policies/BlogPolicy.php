<?php

namespace Dewsign\NovaBlog\Policies;

use Illuminate\Support\Facades\Gate;
use Illuminate\Auth\Access\HandlesAuthorization;

class BlogPolicy
{
    use HandlesAuthorization;

    public function viewAny()
    {
        return Gate::any(['viewBlog', 'manageBlog']);
    }

    public function view($model)
    {
        return Gate::any(['viewBlog', 'manageBlog'], $model);
    }

    public function create($user)
    {
        return $user->can('manageBlog');
    }

    public function update($user, $model)
    {
        return $user->can('manageBlog', $model);
    }

    public function delete($user, $model)
    {
        return $user->can('manageBlog', $model);
    }

    public function restore($user, $model)
    {
        return $user->can('manageBlog', $model);
    }

    public function forceDelete($user, $model)
    {
        return $user->can('manageBlog', $model);
    }
}
