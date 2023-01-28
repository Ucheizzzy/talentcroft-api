<?php

namespace App\Repositories;

use App\Models\User;
use App\Repositories\BaseRepository;

class UserRepository extends BaseRepository
{

    public function __construct(User $user)
    {
        parent::__construct($user);
        $this->user = $user;
    }

      /**
     * @param User $user
     * @return User
     */
    final public function prepareUserData(User $user): User
    {
        $user->append(['name', 'metrics']);
        $user->posts;
        $user->setting;
        return $user;
    }

}
