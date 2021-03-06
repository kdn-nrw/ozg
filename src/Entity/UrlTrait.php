<?php
/**
 * This file is part of the KDN OZG package.
 *
 * @author    Gert Hammes <info@gerthammes.de>
 * @copyright 2019 Gert Hammes
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;


/**
 * Url trait
 */
trait UrlTrait
{

    /**
     * Url
     * @var string|null
     *
     * @ORM\Column(type="string", length=2048, nullable=true)
     */
    private $url;

    /**
     * @return string|null
     */
    public function getUrl(): ?string
    {
        if (!empty($this->url) && strpos($this->url, 'http') !== 0) {
            return 'https://' . ltrim($this->url, '/ ');
        }
        return $this->url;
    }

    /**
     * @param string|null $url
     */
    public function setUrl(?string $url): void
    {
        $this->url = $url;
    }
}
