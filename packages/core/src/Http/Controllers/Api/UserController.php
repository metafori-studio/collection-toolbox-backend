<?php

namespace Metafori\Core\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Metafori\Core\Http\Resources\UserResource;

class UserController extends Controller
{
    public function me(Request $request): UserResource
    {
        return new UserResource($request->user());
    }
}
