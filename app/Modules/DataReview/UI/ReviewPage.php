<?php
namespace App\Modules\DataReview\UI;
use Livewire\Component;
use App\Modules\DataReview\Queries\ReviewStatus;
class ReviewPage extends Component
{
    public function boot(): void { abort_unless(ReviewStatus::allowed(),403); }
    public function render() { return view('data-review.page',app(ReviewStatus::class)->execute())->layout('components.layouts.app'); }
}
