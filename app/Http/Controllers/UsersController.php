<?php

namespace App\Http\Controllers;

use App\Filters\UserFilter;
use App\Forms\ImageRequest;
use App\Forms\UserRequest;
use App\Models\User;
use App\Transformers\UserTransformer;
use Dingo\Api\Http\Request;
use Dingo\Api\Http\Response;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use League\Fractal\Resource\Item;
use Symfony\Component\CssSelector\Exception\InternalErrorException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class UsersController extends Controller
{

    /**
     * @param Request $request
     * @throws \Illuminate\Validation\ValidationException
     */
    public function login()
    {
        return view('user.login');
    }

    public function authCustom(Request $request)
    {
        $this->validate($request, [
            'email' => 'required', 'email',
            'password' => 'required', 'string', 'max:255', Password::min(6)
        ]);

        $email = $request->input('email');
        $password = $request->input('password');
        $user = User::where('email', $email)->first();

        if ($user && !in_array($user->role, [User::ROLE_TRAINER, User::ROLE_SPORTSMAN, User::ROLE_ADMIN])) {
            throw new HttpException(401, 'Failed to authorize user, only users with role [trainer, sportsman, admin] can be authorized');
        }

        if ($user && Hash::check($password, $user->password)) {
            if ($user->authNotExpired() === false) {
                $user->refreshAuthToken()->save();
            }

            $this->fractal->setSerializer(new \App\Serializers\CustomJsonApiSerializer());
            $item = new Item($user, new UserTransformer(['behaviorProvideAuth']), 'user');
            return $this->response->array($this->fractal->createData($item, 'user')->toArray() + ['status_code' => 200]);
        }

        throw new HttpException(422, 'Failed to authorize user, password or email is incorrect');
    }

    /**
     * @param UserRequest $request
     * @return \Dingo\Api\Http\Response
     */
    public function add(UserRequest $request)
    {
        $user = User::create($request->all());
        $this->bbHelper->saveUserPhoto($request, $user);
        $this->fractal->setSerializer(new \App\Serializers\CustomJsonApiSerializer());
        $item = new Item($user, new UserTransformer(['behaviorProvideAuth']), 'user');
        return $this->response->array($this->fractal->createData($item, 'user')->toArray() + ['status_code' => 200]);
    }

    /**
     * @param Request $request
     * @param $id
     * @return Response
     */
    public function uploadImage(Request $request, $id)
    {

        $user = User::where('id', (int)$id)
            ->take(1)
            ->first();

        if (!$user) {
            throw new NotFoundHttpException('User not found');
        }

        $imageName = 'photo_image';
        $imageRequest = new ImageRequest($request->all(), $request->all(), [], [], [], [], $imageName);
        $imageRequest->validate();
        if (!$this->bbHelper->saveUserPhoto($request, $user)) {
            throw new InternalErrorException('Internal Error. Failed to upload image', 500);
        }

        $this->fractal->setSerializer(new \App\Serializers\CustomJsonApiSerializer());
        $item = new Item($user, new UserTransformer, 'user');
        return $this->response->array($this->fractal->createData($item, 'user')->toArray() + ['status_code' => 200]);
    }

    /**
     * @param UserRequest $request
     * @param $id
     * @return \Dingo\Api\Http\Response
     */
    public function edit(Request $request, $id)
    {
        $user = User::where('id', (int)$id)
            ->take(1)
            ->first();

        if (!$user) {
            throw new NotFoundHttpException('User not found');
        }

        $userRequest = new UserRequest($request->all(), $request->all(), [], [], [], [], $user);
        $userRequest->validate();
        $this->bbHelper->saveUserPhoto($request, $user);
        $user->update($request->post());
        $this->fractal->setSerializer(new \App\Serializers\CustomJsonApiSerializer());
        $item = new Item($user, new UserTransformer, 'user');
        return $this->response->array($this->fractal->createData($item, 'user')->toArray() + ['status_code' => 200]);
    }


    /**
     * @param $id
     * @return \Dingo\Api\Http\Response|void
     */
    public function remove($id)
    {
        $user = User::find($id);

        if (!$user) {
            throw new NotFoundHttpException('User not found');
        }

        if ($user->delete()) {
            return $this->response->array([
                'status' => true,
                'status_code' => 204
            ]);
        }

        return $this->response->error('Internal error', 500);
    }


    /**
     * @param Request $request
     * @return \Dingo\Api\Http\Response
     */
    public function listing(Request $request)
    {
        $users = User::limit($request->input('per_page', self::DEFAULT_PER_PAGE));
        (new UserFilter())->applySorts($users, $request);
        $users = $users->paginate($request->input('per_page', self::DEFAULT_PER_PAGE));
        return $this->response->paginator($users, new UserTransformer(), ['key' => 'user']);
    }


    public function search(Request $request)
    {
        $search = $request->get('search_text');

        if (!$search) {
            throw new BadRequestHttpException('Missing parameter [search_text]');
        }

        $users = User::where('email', 'LIKE', "%{$search}%")
            ->orWhere('role', 'LIKE', "%{$search}%")
            ->orWhere('first_name', 'LIKE', "%{$search}%")
            ->orWhere('last_name', 'LIKE', "%{$search}%")
            ->orWhere('gender', 'LIKE', "%{$search}%")
            ->orderBy('email', 'ASC')
            ->orderBy('role', 'ASC')
            ->orderBy('first_name', 'ASC')
            ->orderBy('last_name', 'ASC')
            ->orderBy('gender', 'ASC')
            ->take($request->input('per_page', self::DEFAULT_PER_PAGE))
            ->get();

        return $this->response->collection($users, new UserTransformer(), ['key' => 'user']);
    }

    /**
     * @param $id
     */
    public function info($id)
    {
        $user = User::where('id', (int)$id)
            ->take(1)
            ->first();

        if (!$user) {
            throw new NotFoundHttpException('User not found');
        }

        return $this->response->item($user, new UserTransformer, ['key' => 'user']);
    }
}
