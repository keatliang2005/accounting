<?php

namespace Scottlaurent\Accounting\Models;

use Illuminate\Database\Eloquent\Model;
use Money\Currencies\ISOCurrencies;
use Money\Currency;
use Money\Formatter\IntlMoneyFormatter;
use Money\Money;

/**
 * Class Ledger
 *
 * @package Scottlaurent\Accounting
 * @property    int $journal_id
 * @property    int $debit
 * @property    int $credit
 * @property    string $currency
 * @property    string memo
 * @property    \Carbon\Carbon $post_date
 * @property    \Carbon\Carbon $updated_at
 * @property    \Carbon\Carbon $created_at
 */
class JournalTransaction extends Model
{

    /**
     * @var string
     */
    protected $table = 'journal_transactions';

    /**
     * @var string
     */
    protected $currency = 'USD';

    protected $appends = ['debit_ISO', 'credit_ISO'];

    /**
     * @var IntlMoneyFormatter|null
     */
    protected $moneyFormatter;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $currencies = new ISOCurrencies();
        $numberFormatter = new \NumberFormatter('en_US',
            \NumberFormatter::CURRENCY);
        $this->moneyFormatter = new IntlMoneyFormatter($numberFormatter,
            $currencies);
    }

    /**
     * @var array
     */
    protected $casts
        = [
            'post_date' => 'datetime',
            'tags'      => 'array',
        ];

    protected static function boot()
    {
        parent::boot();

        static::saved(function ($transaction) {
            $transaction->journal->resetCurrentBalances();
        });

        static::deleted(function ($transaction) {
            $transaction->journal->resetCurrentBalances();
        });
    }

    public function getdebitISOAttribute($value)
    {
        if($this->debit){
            $money = new Money($this->debit, new Currency($this->getAttribute('currency')));
            return $this->moneyFormatter->format($money);
        }
    }

    public function getCreditISOAttribute($value)
    {
        if($this->credit){
            $money = new Money($this->credit, new Currency($this->getAttribute('currency')));
            return $this->moneyFormatter->format($money);
        }
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function journal()
    {
        return $this->belongsTo(Journal::class);
    }

    /**
     * Set Reference of the transaction
     *
     * @param  Model  $object
     *
     * @return JournalTransaction
     */
    public function referencesObject($object)
    {
        $this->ref_class = get_class($object);
        $this->ref_class_id = $object->id;
        $this->save();

        return $this;
    }


    /**
     * GET reference of the transaction
     */
    public function getReferencedObject()
    {
        if ($classname = $this->ref_class) {
            $_class = new $this->ref_class;

            return $_class->find($this->ref_class_id);
        }

        return false;
    }

    public function reference()
    {
        return $this->morphTo('', 'ref_class', 'ref_class_id', 'id');
    }

    /**
     * @param  string  $currency
     */
    public function setCurrency($currency)
    {
        $this->currency = $currency;
    }

}
