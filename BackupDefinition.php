<?php

namespace Lumenite\Backer;

use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Lumenite\Backer\Policies\Policy;

/**
 * @author Mohammed Mudassir <hello@mudasir.me>
 */
abstract class BackupDefinition
{
    /**
     * Disable foreign key
     *
     * @var bool disableForeignKey
     */
    public $disableForeignKey = false;

    /**
     * hard delete the record
     *
     * @var bool disablePrimaryTableRecords
     */
    public $deleteAfterArchive = false;

    /** @var string $sortBy */
    public $sortBy = 'created_at';

    /** @var Carbon|null $endDate */
    protected $endDate;

    /** @var Carbon|null $startDate */
    protected $startDate;

    /**
     * @param Carbon|null $endDate
     * @param Carbon|null $startDate
     */
    public function __construct(Carbon $endDate = null, Carbon $startDate = null)
    {
        $this->endDate = $endDate;
        $this->startDate = $startDate;
    }

    /**
     * @return string
     */
    abstract public function table(): string;

    /**
     * @return string
     */
    abstract public function model(): string;

    /**
     * @param $model Builder|Model
     * @return Builder|Model
     */
    abstract public function filter($model = null);

    /**
     * @param Builder|\Illuminate\Database\Eloquent\Model|null $model
     * @return Builder|\Illuminate\Database\Eloquent\Model
     * @throws Exception
     */
    protected function getModelInstance($model = null)
    {
        if (! $model) {
            return app($this->model());
        }

        if ($model instanceof Builder or is_a($model, $this->model())) {
            return $model;
        }

        throw new Exception(sprintf(
            '%s is not an instance of %s or %s',
            $model,
            Builder::class,
            $this->model()
        ));
    }

    /**
     * @param Model|Builder $model
     * @return mixed
     */
    protected function attachStartAndEndDate($model)
    {
        return $model
            ->where(function (Builder $userLog) {
                if ($this->startDate) {
                    $userLog->where($this->sortBy, '>', $this->startDate);
                }

                if ($this->endDate) {
                    $userLog->where($this->sortBy, '<', $this->endDate);
                }
            });
    }

    /**
     * @return array|Policy[]
     */
    public function policies()
    {
        return [];
    }
}
