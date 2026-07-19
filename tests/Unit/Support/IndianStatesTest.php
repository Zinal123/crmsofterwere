<?php

namespace Tests\Unit\Support;

use App\Support\IndianStates;
use PHPUnit\Framework\TestCase;

class IndianStatesTest extends TestCase
{
    public function test_list_contains_36_states_and_union_territories(): void
    {
        $this->assertCount(36, IndianStates::LIST);
    }

    public function test_list_contains_gujarat_and_maharashtra(): void
    {
        $this->assertContains('Gujarat', IndianStates::LIST);
        $this->assertContains('Maharashtra', IndianStates::LIST);
    }

    public function test_list_has_no_duplicates(): void
    {
        $this->assertCount(count(IndianStates::LIST), array_unique(IndianStates::LIST));
    }

    public function test_home_state_is_gujarat(): void
    {
        $this->assertSame('Gujarat', IndianStates::HOME_STATE);
    }

    public function test_home_state_is_itself_in_the_list(): void
    {
        $this->assertContains(IndianStates::HOME_STATE, IndianStates::LIST);
    }
}
