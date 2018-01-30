<?php

namespace Eccube\Tests\Repository;

use Eccube\Entity\Member;
use Eccube\Repository\MemberRepository;
use Eccube\Tests\EccubeTestCase;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;

/**
 * MemberRepository test cases.
 *
 * @author Kentaro Ohkouchi
 */
class MemberRepositoryTest extends EccubeTestCase
{
    /** @var  Member */
    protected $Member;
    /** @var  MemberRepository */
    protected $memberRepo;

    /** @var  EncoderFactoryInterface */
    protected $encoderFactory;
    public function setUp()
    {
        parent::setUp();
        $this->encoderFactory = $this->container->get('security.encoder_factory');
        $this->memberRepo = $this->container->get(MemberRepository::class);
        $this->Member = $this->memberRepo->find(1);
        $Work = $this->entityManager->getRepository('Eccube\Entity\Master\Work')
            ->find(\Eccube\Entity\Master\Work::WORK_ACTIVE_ID);

        for ($i = 0; $i < 3; $i++) {
            $Member = new Member();
            $salt = bin2hex(openssl_random_pseudo_bytes(5));
            $password = 'password';
            $encoder = $this->encoderFactory->getEncoder($Member);
            $Member
                ->setLoginId('member-1')
                ->setPassword($encoder->encodePassword($password, $salt))
                ->setSalt($salt)
                ->setSortNo($i)
                ->setWork($Work);
            $this->entityManager->persist($Member);
            $this->memberRepo->save($Member);
        }
    }

    public function testUp()
    {
        $sortNo = $this->Member->getSortNo();
        $this->memberRepo->up($this->Member);

        $this->expected = $sortNo + 1;
        $this->actual = $this->Member->getSortNo();
        $this->verify();
    }

    public function testUpWithException()
    {
        $this->expectException(\Exception::class);
        $this->Member->setSortNo(999);
        $this->entityManager->flush();

        $this->memberRepo->up($this->Member);
    }

    public function testDown()
    {
        $qb = $this->entityManager->createQueryBuilder();
        $max = $qb->select('MAX(m.sort_no)')
            ->from(Member::class, 'm')
            ->getQuery()
            ->getSingleScalarResult();

        $this->Member->setSortNo($max + 1);
        $this->entityManager->flush();

        $sortNo = $this->Member->getSortNo();
        $this->memberRepo->down($this->Member);

        $this->expected = $sortNo - 1;
        $this->actual = $this->Member->getSortNo();
        $this->verify();
    }

    public function testDownWithException()
    {
        $this->expectException(\Exception::class);
        $this->Member->setSortNo(0);
        $this->entityManager->flush();

        $this->memberRepo->down($this->Member);
        $this->fail();
    }

    public function testSave()
    {
        $Member = new Member();
        $Member
            ->setLoginId('member-100')
            ->setPassword('password')
            ->setSalt('salt')
            ->setSortNo(100);

        $this->memberRepo->save($Member);

        // verify
        $member = $this->memberRepo->findOneBy(array('login_id' => 'member-100'));
        $this->actual = $member->getPassword();
        $this->expected = $Member->getPassword();
        $this->verify();
    }

    public function testSaveWithSortNoNull()
    {
        $qb = $this->entityManager->createQueryBuilder();
        $sortNo = $qb->select('MAX(m.sort_no)')
            ->from(Member::class, 'm')
            ->getQuery()
            ->getSingleScalarResult();

        $Member = new Member();
        $Member
            ->setLoginId('member-100')
            ->setPassword('password')
            ->setSalt('salt')
            ->setSortNo(100);
        $this->memberRepo->save($Member);

        $this->expected = $sortNo + 1;
        $this->actual = $Member->getSortNo();

        $this->verify();
    }

    public function testDelete()
    {
        $Member = $this->createMember();
        $id = $Member->getId();
        $this->memberRepo->delete($Member);

        $Member = $this->memberRepo->find($id);
        $this->assertNull($Member);
    }

    public function testDeleteWithException()
    {
        $this->expectException(\Exception::class);
        $Member1 = $this->createMember();
        $Member2 = $this->createMember();
        $Member2->setCreator($Member1);
        $this->entityManager->flush();

        // 参照制約で例外となる
        $this->memberRepo->delete($Member1);
        $this->fail();
    }
}
