<?php
/**
 * @link    https://github.com/igorsantos07/yii-br-validator
 * @license https://github.com/igorsantos07/yii-br-validator/blob/master/LICENSE
 */

/**
 * CpfValidator checks if the attribute value is a valid CPF.
 *
 * @author Igor Santos <igorsantos07@gmail.com>
 * @author Leandro Gehlen <leandrogehlen@gmail.com>
 * @author Wanderson Bragança <wanderson.wbc@gmail.com>
 */
class Cpf extends CValidator {

	public $allowEmpty;

	/**
	 * @inheritdoc
	 */
	public function __construct() {
		if ($this->message === null) {
			$this->message = Yii::t('yii', '{attribute} is invalid.');
		}
	}

	/**
	 * @inheritdoc
	 */
	protected function validateAttribute($object, $attribute) {
		if (!$object->$attribute && $this->allowEmpty) return;

		$valid = true;
		$cpf   = str_pad(preg_replace('/[^0-9_]/', '', $object->$attribute), 11, '0', STR_PAD_LEFT);

		for ($x = 0; $x <= 9; $x++) {
			if ($cpf == str_repeat($x, 11)) {
				$valid = false;
			}
		}

		if ($valid) {
			if (strlen($cpf) != 11) {
				$valid = false;
			}
			else {
				for ($t = 9; $t < 11; $t++) {
					$d = 0;
					for ($c = 0; $c < $t; $c++) {
						$d += $cpf{$c} * (($t + 1) - $c);
					}
					$d = ((10 * $d) % 11) % 10;
					if ($cpf{$c} != $d) {
						$valid = false;
						break;
					}
				}
			}
		}

		if (!$valid) {
			$this->addError($object, $attribute, $this->message);
		}
	}
}