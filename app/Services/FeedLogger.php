<?php namespace App\Services;
/**
 * @author: wanghui
 * @date: 2017/10/25 下午8:17
 * @email: wanghui@yonglibao.com
 */

use App\Models\Feed\Feed;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Traits\Macroable;

/**
 * Class FeedLogger
 * @package App\Services
 * Example:
 * feed()
->performedOn($article)
->causedBy($user)
->withProperties(['laravel' => 'awesome'])
->log('The subject name is :performedOn.name, the causer name is :causedBy.name and Laravel is :properties.laravel',1);
 */
class FeedLogger
{
    use Macroable;

    protected $anonymous = 0;

    /** @var \Illuminate\Database\Eloquent\Model */
    protected $performedOn;

    /** @var \Illuminate\Database\Eloquent\Model */
    protected $causedBy;

    /** @var \Illuminate\Support\Collection */
    protected $properties;

    public function __construct()
    {

        $this->properties = collect();

    }

    public function performedOn(Model $model)
    {
        $this->performedOn = $model;

        return $this;
    }

    public function on(Model $model)
    {
        return $this->performedOn($model);
    }

    public function anonymous($is_anonymous = 0){
        $this->anonymous = $is_anonymous;
        return $this;
    }

    /**
     * @param User $model
     *
     * @return $this
     */
    public function causedBy(User $model)
    {

        $this->causedBy = $model;

        return $this;
    }

    public function by(User $modelOrId)
    {
        return $this->causedBy($modelOrId);
    }

    /**
     * @param array|\Illuminate\Support\Collection $properties
     *
     * @return $this
     */
    public function withProperties($properties)
    {
        $this->properties = collect($properties);

        return $this;
    }

    /**
     * @param string $key
     * @param mixed  $value
     *
     * @return $this
     */
    public function withProperty(string $key, $value)
    {
        $this->properties->put($key, $value);

        return $this;
    }

    /**
     * @param string $description
     * @param string $feedType
     *
     * @return null|mixed
     */
    public function log(string $description, $feedType)
    {

        $activity = new Feed();

        if ($this->performedOn) {
            $activity->source()->associate($this->performedOn);
            $activity->created_at = (string)$this->performedOn->created_at;
        }

        if ($this->causedBy) {
            $activity->user_id = $this->causedBy->id;
        }

        $this->withProperty('feed_content', $this->replacePlaceholders($description));

        $activity->data = $this->properties;

        $activity->feed_type = $feedType;

        $activity->is_anonymous = $this->anonymous;

        $activity->save();

        return $activity;
    }

    protected function replacePlaceholders(string $description): string
    {
        return preg_replace_callback('/:[a-z0-9._-]+/i', function ($match) {
            $match = $match[0];

            $attribute = (string) string($match)->between(':', '.');

            if (! in_array($attribute, ['performedOn', 'causedBy', 'properties'])) {
                return $match;
            }

            $propertyName = substr($match, strpos($match, '.') + 1);

            $attributeValue = $this->$attribute;

            if (is_null($attributeValue)) {
                return $match;
            }

            $attributeValue = $attributeValue->toArray();

            return array_get($attributeValue, $propertyName, $match);
        }, $description);
    }
}
