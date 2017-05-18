<?php
declare(strict_types=1);
/**
 * /tests/Integration/Resource/GenericResourceTest.php
 *
 * @author  TLe, Tarmo Leppänen <tarmo.leppanen@protacon.com>
 */
namespace Integration\Resource;

use App\Entity\Interfaces\EntityInterface;
use App\Entity\User as UserEntity;
use App\Repository\UserRepository;
use App\Resource\UserResource;
use App\Rest\DTO\User as UserDto;
use App\Rest\Interfaces\Resource as ResourceInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use PHPUnit_Framework_MockObject_MockBuilder;
use PHPUnit_Framework_MockObject_MockObject;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Class GenericResourceTest
 *
 * @package Integration\Resource
 * @author  TLe, Tarmo Leppänen <tarmo.leppanen@protacon.com>
 */
class GenericResourceTest extends KernelTestCase
{
    protected $dtoClass = UserDto::class;
    protected $entityClass = UserEntity::class;
    protected $resourceClass = UserResource::class;
    protected $repositoryClass = UserRepository::class;

    /**
     * @return ValidatorInterface
     */
    private static function getValidator(): ValidatorInterface
    {
        return static::$kernel->getContainer()->get('validator');
    }

    /**
     * @return EntityManagerInterface|Object
     */
    private static function getEntityManager(): EntityManagerInterface
    {
        return static::$kernel->getContainer()->get('doctrine.orm.entity_manager');
    }

    public function setUp(): void
    {
        parent::setUp();

        static::bootKernel();
    }

    /**
     * @expectedException \UnexpectedValueException
     * @expectedExceptionMessage DTO class not specified for
     */
    public function testThatGetDtoClassThrowsAnExceptionWithoutDto(): void
    {
        /** @var PHPUnit_Framework_MockObject_MockObject|UserRepository $repository */
        $repository = $this->getRepositoryMockBuilder()->getMock();

        /** @var ResourceInterface $resource */
        $resource = new $this->resourceClass($repository, self::getValidator());
        $resource->getDtoClass();
    }

    public function testThatGetDtoClassReturnsExpectedDto(): void
    {
        /** @var PHPUnit_Framework_MockObject_MockObject|UserRepository $repository */
        $repository = $this->getRepositoryMockBuilder()->getMock();

        /** @var ResourceInterface $resource */
        $resource = new $this->resourceClass($repository, self::getValidator());
        $resource->setDtoClass('foobar');

        static::assertSame('foobar', $resource->getDtoClass());
    }

    public function testThatGetEntityNameCallsExpectedRepositoryMethod(): void
    {
        /** @var PHPUnit_Framework_MockObject_MockObject|UserRepository $repository */
        $repository = $this->getRepositoryMockBuilder()->getMock();

        $repository
            ->expects(static::once())
            ->method('getEntityName');

        /** @var ResourceInterface $resource */
        $resource = new $this->resourceClass($repository, self::getValidator());
        $resource->getEntityName();
    }

    public function testThatGetReferenceCallsExpectedRepositoryMethod(): void
    {
        /** @var PHPUnit_Framework_MockObject_MockObject|UserRepository $repository */
        $repository = $this->getRepositoryMockBuilder()->getMock();

        $repository
            ->expects(static::once())
            ->method('getReference');

        /** @var ResourceInterface $resource */
        $resource = new $this->resourceClass($repository, self::getValidator());
        $resource->getReference('some id');
    }

    public function testThatGetAssociationsCallsExpectedRepositoryMethod(): void
    {
        /** @var PHPUnit_Framework_MockObject_MockObject|UserRepository $repository */
        $repository = $this->getRepositoryMockBuilder()->getMock();

        $repository
            ->expects(static::once())
            ->method('getAssociations');

        /** @var ResourceInterface $resource */
        $resource = new $this->resourceClass($repository, self::getValidator());
        $resource->getAssociations();
    }

    /**
     * @dataProvider dataProviderTestThatFindCallsExpectedRepositoryMethodWithCorrectParameters
     *
     * @param array $expectedArguments
     * @param array $arguments
     */
    public function testThatFindCallsExpectedRepositoryMethodWithCorrectParameters(
        array $expectedArguments,
        array $arguments
    ): void
    {
        /** @var PHPUnit_Framework_MockObject_MockObject|UserRepository $repository */
        $repository = $this->getRepositoryMockBuilder()->getMock();

        $repository
            ->expects(static::once())
            ->method('findByAdvanced')
            ->with(...$expectedArguments);

        /** @var ResourceInterface $resource */
        $resource = new $this->resourceClass($repository, self::getValidator());
        $resource->find(...$arguments);
    }

    public function testThatFindOneCallsExpectedRepositoryMethod(): void
    {
        /** @var PHPUnit_Framework_MockObject_MockObject|UserRepository $repository */
        $repository = $this->getRepositoryMockBuilder()->getMock();

        $repository
            ->expects(static::once())
            ->method('find')
            ->withAnyParameters();

        /** @var ResourceInterface $resource */
        $resource = new $this->resourceClass($repository, self::getValidator());
        $resource->findOne('some id');
    }

    /**
     * @expectedException \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     * @expectedExceptionMessage Not found
     */
    public function testThatFindOneThrowsAnExceptionIfEntityWasNotFound(): void
    {
        /** @var PHPUnit_Framework_MockObject_MockObject|UserRepository $repository */
        $repository = $this->getRepositoryMockBuilder()->getMock();

        $repository
            ->expects(static::once())
            ->method('find')
            ->withAnyParameters()
            ->willReturn(null);

        /** @var ResourceInterface $resource */
        $resource = new $this->resourceClass($repository, self::getValidator());
        $resource->findOne('some id', true);
    }

    public function testThatFindOneWontThrowAnExceptionIfEntityWasFound(): void
    {
        $entity = $this->getEntityInterfaceMock();

        /** @var PHPUnit_Framework_MockObject_MockObject|UserRepository $repository */
        $repository = $this->getRepositoryMockBuilder()->getMock();

        $repository
            ->expects(static::once())
            ->method('find')
            ->withAnyParameters()
            ->willReturn($entity);

        /** @var ResourceInterface $resource */
        $resource = new $this->resourceClass($repository, self::getValidator());

        static::assertSame($entity, $resource->findOne('some id', true));
    }

    /**
     * @dataProvider dataProviderTestThatFindOneByCallsExpectedRepositoryMethodWithCorrectParameters
     *
     * @param array $expectedArguments
     * @param array $arguments
     */
    public function testThatFindOneByCallsExpectedRepositoryMethodWithCorrectParameters(
        array $expectedArguments,
        array $arguments
    ): void
    {
        /** @var PHPUnit_Framework_MockObject_MockObject|UserRepository $repository */
        $repository = $this->getRepositoryMockBuilder()->getMock();

        $repository
            ->expects(static::once())
            ->method('findOneBy')
            ->with(...$expectedArguments);

        /** @var ResourceInterface $resource */
        $resource = new $this->resourceClass($repository, self::getValidator());
        $resource->findOneBy(...$arguments);
    }

    /**
     * @expectedException \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     * @expectedExceptionMessage Not found
     */
    public function testThatFindOneByThrowsAnExceptionIfEntityWasNotFound(): void
    {
        /** @var PHPUnit_Framework_MockObject_MockObject|UserRepository $repository */
        $repository = $this->getRepositoryMockBuilder()->getMock();

        $repository
            ->expects(static::once())
            ->method('findOneBy')
            ->withAnyParameters()
            ->willReturn(null);

        /** @var ResourceInterface $resource */
        $resource = new $this->resourceClass($repository, self::getValidator());
        $resource->findOneBy([], null, true);
    }

    public function testThatFindOneByWontThrowAnExceptionIfEntityWasFound(): void
    {
        $entity = $this->getEntityInterfaceMock();

        /** @var PHPUnit_Framework_MockObject_MockObject|UserRepository $repository */
        $repository = $this->getRepositoryMockBuilder()->getMock();

        $repository
            ->expects(static::once())
            ->method('findOneBy')
            ->withAnyParameters()
            ->willReturn($entity);

        /** @var ResourceInterface $resource */
        $resource = new $this->resourceClass($repository, self::getValidator());

        static::assertSame($entity, $resource->findOneBy([], null, true));
    }

    /**
     * @dataProvider dataProviderTestThatCountCallsExpectedRepositoryMethodWithCorrectParameters
     *
     * @param array $expectedArguments
     * @param array $arguments
     */
    public function testThatCountCallsExpectedRepositoryMethodWithCorrectParameters(
        array $expectedArguments,
        array $arguments
    ): void
    {
        /** @var PHPUnit_Framework_MockObject_MockObject|UserRepository $repository */
        $repository = $this->getRepositoryMockBuilder()->getMock();

        $repository
            ->expects(static::once())
            ->method('count')
            ->with(...$expectedArguments);

        /** @var ResourceInterface $resource */
        $resource = new $this->resourceClass($repository, self::getValidator());
        $resource->count(...$arguments);
    }

    public function testThatSaveMethodCallsExpectedRepositoryMethod(): void
    {
        $entity = $this->getEntityInterfaceMock();

        /** @var PHPUnit_Framework_MockObject_MockObject|UserRepository $repository */
        $repository = $this->getRepositoryMockBuilder()->getMock();

        $repository
            ->expects(static::once())
            ->method('save')
            ->with($entity);

        /** @var ResourceInterface $resource */
        $resource = new $this->resourceClass($repository, self::getValidator());

        static::assertSame($entity, $resource->save($entity));
    }

    /**
     * @expectedException \Symfony\Component\Validator\Exception\ValidatorException
     */
    public function testThatCreateThrowsAnErrorWithInvalidDto(): void
    {
        $dto = new $this->dtoClass();

        /** @var PHPUnit_Framework_MockObject_MockObject|UserRepository $repository */
        $repository = $this->getRepositoryMockBuilder()->getMock();

        /** @var ResourceInterface $resource */
        $resource = new $this->resourceClass($repository, self::getValidator());
        $resource->create($dto);
    }

    /**
     * @expectedException \Symfony\Component\Validator\Exception\ValidatorException
     */
    public function testThatSaveMethodThrowsAnExceptionWithInvalidEntity(): void
    {
        $entity = new $this->entityClass();

        /** @var PHPUnit_Framework_MockObject_MockObject|UserRepository $repository */
        $repository = $this->getRepositoryMockBuilder()->getMock();

        $repository
            ->expects(static::never())
            ->method('save')
            ->with($entity);

        /** @var ResourceInterface $resource */
        $resource = new $this->resourceClass($repository, self::getValidator());
        $resource->save($entity);
    }

    /**
     * @return array
     */
    public function dataProviderTestThatCountCallsExpectedRepositoryMethodWithCorrectParameters(): array
    {
        return [
            [
                [[], []],
                [null, null],
            ],
            [
                [['foo'], []],
                [['foo'], null],
            ],
            [
                [['foo'], ['bar']],
                [['foo'], ['bar']],
            ],
        ];
    }

    /**
     * @return array
     */
    public function dataProviderTestThatFindCallsExpectedRepositoryMethodWithCorrectParameters(): array
    {
        return [
            [
                [[], [], 0, 0, []],
                [null, null, null, null, null],
            ],
            [
                [['foo'], [], 0, 0, []],
                [['foo'], null, null, null, null],
            ],
            [
                [['foo'], ['foo'], 0, 0, []],
                [['foo'], ['foo'], null, null, null],
            ],
            [
                [['foo'], ['foo'], 1, 0, []],
                [['foo'], ['foo'], 1, null, null],
            ],
            [
                [['foo'], ['foo'], 1, 2, []],
                [['foo'], ['foo'], 1, 2, null],
            ],
            [
                [['foo'], ['foo'], 1, 2, ['foo']],
                [['foo'], ['foo'], 1, 2, ['foo']],
            ],
        ];
    }

    /**
     * @return array
     */
    public function dataProviderTestThatFindOneByCallsExpectedRepositoryMethodWithCorrectParameters(): array
    {
        return [
            [
                [[], []],
                [[], null],
            ],
            [
                [['foo'], []],
                [['foo'], null],
            ],
            [
                [['foo'], ['bar']],
                [['foo'], ['bar']],
            ],
        ];
    }

    /**
     * @return PHPUnit_Framework_MockObject_MockBuilder
     */
    private function getRepositoryMockBuilder(): PHPUnit_Framework_MockObject_MockBuilder
    {
        return $this
            ->getMockBuilder(UserRepository::class)
            ->setConstructorArgs([self::getEntityManager(), new ClassMetadata($this->entityClass)]);
    }

    /**
     * @return PHPUnit_Framework_MockObject_MockObject|EntityInterface
     */
    private function getEntityInterfaceMock(): PHPUnit_Framework_MockObject_MockObject
    {
        return $this
            ->getMockBuilder(EntityInterface::class)
            ->getMock();
    }
}
