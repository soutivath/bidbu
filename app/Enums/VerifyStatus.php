<?php
namespace App\Enums;
abstract class VerifyStatus extends Enum {
    const PENDING = "pending";
    const REJECTED = "rejected";
    const APPROVED = "approved";
}