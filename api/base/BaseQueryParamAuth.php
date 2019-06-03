<?php
/**
 * Created by PhpStorm.
 * User: tangming
 * Date: 8/21/2015
 * Time: 11:01 AM
 */

namespace api\base;


use yii\filters\auth\AuthMethod;

class BaseQueryParamAuth extends AuthMethod
{
    public $tokenParam = 'access_token';

    /**
     * @inheritdoc
     */
    public function authenticate($user, $request, $response)
    {
        $accessToken = trim($request->get($this->tokenParam));
        if (is_string($accessToken)) {
            $identity = $user->loginByAccessToken($accessToken, get_class($this));
            if ($identity !== null) {
                return $identity;
            }
        }
        if ($accessToken !== null) {
            $this->handleFailure($response);
        }

        return null;
    }
}