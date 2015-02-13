<?php

class Notas {
	protected $params;

	public function __construct($params) {
		$this->params = $params;
	}

	public function index() {
		return "listado de notas";
	}

	public function create() {
		return "crear una nota";
	}

	public function read() {
		return "devolver una nota por id";
	}

	public function update() {
		return "actualizar una nota por id";
	}

	public function bulkUpdate() {
		return "actualizar varias notas";
	}

	public function delete() {
		return "borrar nota por id";
	}

	public function bulkDelete() {
		return "borrar todo";
	}
}