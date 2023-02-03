<?php

use Dicibi\EloquentModification\Tests\Helper;

use function PHPUnit\Framework\assertCount;
use function PHPUnit\Framework\assertEquals;
use function PHPUnit\Framework\assertTrue;

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
    /** @var \Dicibi\EloquentModification\Tests\Models\NormalModel $model */
    $model = $helper->normal()->createModel();

    $oldData = $model->data;

    $data = $model->data;
    $data['foo'] = $newData = $helper->faker->text(10);

    $model->data = $data;
    $model->save();

    assertCount(1, $modifications = $model->modifications);

    $modification = $modifications->first();

    // payload will only record raw data, meaning it will be json encoded
    assertTrue(is_string($modification->payloads->data));
    $payloadsData = json_decode($modification->payloads->data);

    assertEquals($newData, $payloadsData->foo);

    // unlike payloads, state will record the casting value, meaning it will be an array
    assertEquals($oldData['foo'], $modification->state->data->foo);

    // try wayback machine
    //
    //


    // change data
    $data = $model->data;
    $data['foo'] = $helper->faker->text(10);

    $model->data = $data;
    $model->save();

    $model->usingModification($modification);

    assertEquals($newData, $model->data['foo']);
});
