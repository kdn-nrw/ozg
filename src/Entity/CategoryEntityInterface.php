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


use Doctrine\Common\Collections\Collection;

/**
 * Category entity interface
 */
interface CategoryEntityInterface
{
    /**
     * @return CategoryEntityInterface[]|Collection
     */
    public function getChildren();

    /**
     * @return CategoryEntityInterface|null
     */
    public function getParent(): ?CategoryEntityInterface;

}
