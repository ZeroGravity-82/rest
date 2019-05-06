<?php

namespace KnpU\CodeBattle\Model;

use Symfony\Component\Validator\Constraints as Assert;
use Hateoas\Configuration\Annotation as Hateoas;
use JMS\Serializer\Annotation as Serializer;

/**
 * Class Programmer
 * @Serializer\ExclusionPolicy("all")
 * @Hateoas\Relation(
 *     "self",
 *     href = @Hateoas\Route(
 *          "api_programmers_show",
 *          parameters={"nickname" = "expr(object.nickname)"}
 *     )
 * )
 */
class Programmer
{

    /* All public properties are persisted */
    public $id;

    /**
     * @Assert\NotBlank(message="Enter non-empty nickname")
     * @Serializer\Expose
     */
    public $nickname;

    /**
     * Number of an avatar, from 1-6
     *
     * @var integer
     * @Serializer\Expose
     */
    public $avatarNumber;

    /**
     * @Assert\NotBlank(message="Enter non-empty tag")
     * @Serializer\Expose
     */
    public $tagLine;

    public $userId;

    /**
     * @Serializer\Expose
     */
    public $powerLevel = 0;

    public function __construct($nickname = null, $avatarNumber = null)
    {
        $this->nickname = $nickname;
        $this->avatarNumber = $avatarNumber;
    }


}
