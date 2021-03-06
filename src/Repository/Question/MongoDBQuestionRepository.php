<?php declare(strict_types=1);


namespace App\Repository\Question;


use App\Service\Response\ErrorService;
use MongoDB\Client;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class MongoDBQuestionRepository implements QuestionRepositoryInterface
{
    private $collection;

    public function __construct()
    {
        $dbName = (string) $_ENV['MONGODB_DB'];
        $this->collection = (new Client($_ENV['MONGODB_URL']))->$dbName->question;
    }

    public function findRandom(int $limit): ?array
    {
        try {
            $questions = $this->collection->aggregate(
                [
                    ['$sample' => ['size' => $limit]],
                    [
                        '$limit' => $limit > 20 ? 1: $limit,
                    ],
                ]
            );
            $questions = $questions->toArray();
        } catch (\Throwable $t) {
            throw new NotFoundHttpException('Data not found');
        }

        return $questions;
    }

    public function insert(array $questions): ?array
    {
        try {
            $questionsInserted = $this->collection->insertMany($questions);
        } catch (\Throwable $e) {
            throw new BadRequestHttpException('An error occured while inserting data');
        }

        return $questionsInserted->getInsertedIds();
    }

    public function deleteAll() {
        $this->collection->deleteMany([]);
    }
}