<?php declare(strict_types=1);

/**
 * This file is part of the BankAccount project.
 *
 * (c) Matthieu Leorat <matthieu.leorat@pm.me>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace App\Tests\Entity;

use App\Entity\Debt;
use App\Entity\User;
use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
    public function test()
    {
        $user = new User();

        $this->assertInstanceOf(ArrayCollection::class, $user->getDebts());
        $debt = new Debt();
        $user->addDebt($debt);
        $this->assertContains($debt, $user->getDebts());
        $user->removeDebt($debt);
        $this->assertCount(0, $user->getDebts());


        $this->assertInstanceOf(ArrayCollection::class, $user->getCredits());
        $credit = new Debt();
        $user->addCredit($credit);
        $this->assertContains($credit, $user->getCredits());
        $user->removeCredit($credit);
        $this->assertCount(0, $user->getCredits());
        
        $this->assertNull($user->getId());

        $username = 'Jack';
        $user->setUsername($username);
        $this->assertEquals($username, $user->getUsername());
        $this->assertEquals($username, $user);

        $password = 'strong password';
        $user->setPassword($password);
        $this->assertEquals($password, $user->getPassword());

        $this->assertNull($user->getSalt());

        $this->assertContains('ROLE_USER', $user->getRoles());
        $this->assertCount(1, $user->getRoles());

        $roles = ['ROLE_ADMIN'];
        $user->setRoles($roles);
        $this->assertContains('ROLE_USER', $user->getRoles());
        $this->assertContains('ROLE_ADMIN', $user->getRoles());
        $this->assertCount(2, $user->getRoles());

        $user->eraseCredentials();
    }
}