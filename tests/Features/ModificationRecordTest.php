<?php

use function PHPUnit\Framework\assertCount;
use function PHPUnit\Framework\assertEquals;
use Dicibi\EloquentModification\Tests\Helper;

$helper = new Helper();

it('can record a modification', function () use ($helper) {
    $model = $helper->normal()->createModel();

    $oldName = $model->name;

    $model->name = $newName = $helper->faker->text(10);
    $model->save();

    assertCount(1, $modifications = $model->modifications);

    $modification = $modifications->first();

    assertEquals($newName, $modification->payloads->name);
    assertEquals($oldName, $modification->state->name);
});

it('can record array value', function () use ($helper) {
    $model = $helper->normal()->createModel();

    $oldData = $model->data;

    $data = $model->data;
    $data['foo'] = $newData = $helper->faker->text(10);

    $model->data = $data;
    $model->save();

    assertCount(1, $modifications = $model->modifications);

    $modification = $modifications->first();

    assertEquals($newData, $modification->payloads->data->foo);
    assertEquals($oldData['foo'], $modification->state->data->foo);
});
