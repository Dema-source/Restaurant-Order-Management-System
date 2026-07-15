<?php

namespace Tests\Traits;

trait WithApiTestSetup
{
    use InteractsWithRoles;
    use InteractsWithResponses;
    use InteractsWithModels;
    use InteractsWithDateRanges;
}