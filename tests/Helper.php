<?php

namespace Dicibi\EloquentModification\Tests;

use Dicibi\EloquentModification\Tests\Models\NormalModel;
use Faker\Factory;
use Faker\Generator;
use Illuminate\Database\Eloquent\Model;
use JetBrains\PhpStorm\ArrayShape;
use JetBrains\PhpStorm\Pure;

class NormalCases
{
    public function __construct(
        protected Generator $faker,
    ) {
    }

    public function createModel(): NormalModel|Model
    {
        return NormalModel::query()->create([
            'name' => $oldName = $this->faker->text(10),
            'description' => $this->faker->paragraph(3),
            'data' => [
                'foo' => $this->faker->text(10),
                'bar' => $this->faker->text(10),
            ],
        ]);
    }

    #[ArrayShape(['name' => "string", 'description' => "string", 'data' => "array"])]
    public function updateModel(NormalModel $normalModel): array
    {
        $newValue = [
            'name' => $this->faker->text(10),
            'description' => $this->faker->paragraph(3),
            'data' => [
                'foo' => $this->faker->text(10),
                'bar' => $this->faker->text(10),
            ],
        ];

        $normalModel->update($newValue);

        return $newValue;
    }
}

class Helper
{
    public Generator $faker;

    public function __construct()
    {
        $this->faker = Factory::create();
    }

    #[Pure]
    public function normal(): NormalCases
    {
        return new NormalCases($this->faker);
    }
}
