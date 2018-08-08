<?php

declare(strict_types=1);

namespace App\Third\Quill\Delta\Html;

/**
 * Delta class for video inserts
 *
 * @author Dean Blackborough <dean@g3d-development.com>
 * @copyright Dean Blackborough
 * @license https://github.com/deanblackborough/php-quill-renderer/blob/master/LICENSE
 */
class Video extends Delta
{
    /**
     * Set the initial properties for the delta
     *
     * @param string $insert
     * @param array $attributes
     */
    public function __construct(string $insert, array $attributes = [])
    {
        $this->tag = null;

        $this->insert = $insert;
        $this->attributes = $attributes;
    }

    /**
     * Render the HTML for the specific Delta type
     *
     * @return string
     */
    public function render(): string
    {
        return '<iframe class="ql-video" frameborder="0" allowfullscreen="true" src="' . $this->escape($this->insert) . '"></iframe>';
    }
}
