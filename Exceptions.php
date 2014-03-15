<?php
class ModelException extends Exception {}

class OperationNotAllowedException extends ModelException {}

class NotSufficientFoundsException extends ModelException {}

class InvalidUserException extends ModelException {}

class InvalidObjectTypeException extends ModelException {}

class InvalidObjectStatusException extends ModelException {}