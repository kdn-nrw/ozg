<?php
/**
 * This file is part of the KDN OZG package.
 *
 * @author    Gert Hammes <info@gerthammes.de>
 * @copyright 2020 Gert Hammes
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Entity;


use App\Entity\Base\NamedEntityInterface;

/**
 * Interface for status entities
 */
interface StatusEntityInterface extends NamedEntityInterface
{

    /**
     * @return StatusEntityInterface|null
     */
    public function getPrevStatus(): ?StatusEntityInterface;

    /**
     * @return StatusEntityInterface|null
     */
    public function getNextStatus(): ?StatusEntityInterface;
}