<?php

declare(strict_types=1);

namespace App\Third\Quill\Delta\GithubMarkdown;

use App\Third\Quill\Options;

/**
 * Delta class for inserts with the 'italic' attribute
 *
 * @author Dean Blackborough <dean@g3d-development.com>
 * @copyright Dean Blackborough
 * @license https://github.com/deanblackborough/php-quill-renderer/blob/master/LICENSE
 */
class Strike extends Delta
{
    /**
     * Set the initial properties for the delta
     *
     * @param string $insert
     * @param array $attributes
     */
    public function __construct(string $insert, array $attributes = [])
    {
        $this->token = Options::GITHUB_MARKDOWN_TOKEN_STRIKE;

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
        return $this->token . $this->escape($this->insert) . $this->token;
    }
}
