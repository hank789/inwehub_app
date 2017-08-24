<?php namespace App\Services;
use App\Models\User;

/**
 * @author: wanghui
 * @date: 2017/8/24 下午3:49
 * @email: wanghui@yonglibao.com
 */

class NotificationSettings

{
    /**
     * The User instance.
     *
     * @var \App\Models\User
     */
    protected $user;

    /**
     * The list of settings.
     *
     * @var array
     */
    protected $settings = [];

    /**
     * Create a new settings instance.
     *
     * @param array $settings
     * @param \App\Models\User $user
     */
    public function __construct(array $settings, User $user)
    {
        $this->settings = $settings;
        $this->user = $user;
    }

    /**
     * Retrieve the given setting.
     *
     * @param string $key
     *
     * @return string
     */
    public function get($key)
    {
        return array_get($this->settings, $key);
    }

    /**
     * Create and persist a new setting.
     *
     * @param string $key
     * @param mixed  $value
     */
    public function set($key, $value)
    {
        $this->settings[$key] = $value;
        $this->persist();
    }

    /**
     * Determine if the given setting exists.
     *
     * @param string $key
     *
     * @return bool
     */
    public function has($key)
    {
        return array_key_exists($key, $this->settings);
    }

    /**
     * Retrieve an array of all settings.
     *
     * @return array
     */
    public function all()
    {
        return $this->settings;
    }

    /**
     * Merge the given attributes with the current settings.
     * But do not assign any new settings.
     *
     * @param array $attributes
     *
     * @return mixed
     */
    public function merge(array $attributes)
    {
        $this->settings = array_merge(
            $this->settings,
            array_only($attributes, array_keys($this->settings))
        );

        return $this->persist();
    }

    /**
     * Persist the settings.
     *
     * @return mixed
     */
    protected function persist()
    {
        return $this->user->update(['site_notifications' => $this->settings]);
    }

    /**
     * Magic property access for settings.
     *
     * @param string $key
     *
     * @return boolean
     */
    public function __get($key)
    {
        if ($this->has($key)) {
            return $this->get($key);
        }
        return true;
    }

}