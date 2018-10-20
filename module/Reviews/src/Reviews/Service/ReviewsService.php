<?php

namespace Reviews\Service;

use Aptero\Service\AbstractService;
use Reviews\Model\Review;
use User\Service\AuthService;

class ReviewsService extends AbstractService
{
    public function addReview($data)
    {
        if($user = AuthService::getUser()) {
            $data['user_id'] =  $user->getId();
        }

        $data['status'] =  Review::STATUS_NEW;

        $review = new Review();
        $review->setVariables($data)->save();
    }
}