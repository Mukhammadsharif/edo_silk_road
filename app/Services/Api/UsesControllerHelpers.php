<?php

namespace App\Services\Api;

use App\Http\Controllers\Controller;

/**
 * Доступ к методам-помощникам базового контроллера из сервиса.
 *
 * ЗАЧЕМ.
 * Логика документов перенесена из контроллеров в сервисы, но опирается
 * на помощники, живущие в App\Http\Controllers\Controller:
 * authActiveUser(), organizationId(), getSignerRole(), createLog(),
 * uniqueHash() и другие. Сервис контроллер не наследует, поэтому без
 * этого трейта вызовы падали бы с «Call to undefined method».
 *
 * Наследовать Controller сервисом было бы неверно: сервис не обслуживает
 * HTTP-запрос и не должен тянуть за собой middleware, валидацию и прочее
 * поведение контроллера. Поэтому держим один экземпляр рядом и перенаправляем
 * вызовы в него — все нужные методы там публичные.
 *
 * Правильное решение на будущее — вынести эти помощники из Controller
 * в собственный сервис: ими пользуются и контроллеры, и теперь сервисы.
 * Трогать базовый класс, от которого наследуется весь проект, сейчас
 * рискованно, поэтому такой переходный вариант.
 */
trait UsesControllerHelpers
{
    /**
     * Имя роли суперадминистратора.
     *
     * В базовом контроллере это свойство ($admin), а не метод, поэтому
     * перенаправление через __call его не покроет — обращение к
     * несуществующему свойству не вызывает __call, а возвращает null
     * с предупреждением. Дублируем значение явно.
     */
    protected $admin = 'Super_Admin';

    private ?Controller $controllerHelpers = null;

    /**
     * Перенаправляем неизвестные вызовы в базовый контроллер.
     *
     * Так сервис не обязан перечислять два десятка помощников поимённо,
     * и добавление нового в Controller не требует правок здесь.
     */
    public function __call(string $method, array $arguments)
    {
        $helpers = $this->controllerHelpers ??= new Controller();

        if (!method_exists($helpers, $method)) {
            throw new \BadMethodCallException(
                static::class . "::{$method}() не найден ни в сервисе, ни в базовом контроллере"
            );
        }

        return $helpers->{$method}(...$arguments);
    }
}
