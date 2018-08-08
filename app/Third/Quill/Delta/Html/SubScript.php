<?php

declare(strict_types=1);

namespace App\Third\Quill\Delta\Html;

use App\Third\Quill\Options;

/**
 * Default delta class for inserts with the 'script' attribute set to 'sub'
 *
 * @author Dean Blackborough <dean@g3d-development.com>
 * @copyright Dean Blackborough
 * @license https://github.com/deanblackborough/php-quill-renderer/blob/master/LICENSE
 */
class SubScript extends Delta
{
    /**
     * Set the initial properties for the delta
     *
     * @param string $insert
     * @param array $attributes
     */
    public function __construct(string $insert, array $attributes = [])
    {
        $this->tag = Options::HTML_TAG_SUB_SCRIPT;

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
        return $this->renderSimpleTag($this->tag, $this->escape($this->insert));
    }
}
