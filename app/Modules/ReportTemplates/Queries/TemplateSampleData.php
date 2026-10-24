<?php
namespace App\Modules\ReportTemplates\Queries;
/** Compatibility adapter: preview received records without fabricating rows or photos. */
class TemplateSampleData
{
    public function execute(int $activityCount=4, ?int $institutionId=null): array
    {
        return app(ReceivedPreviewData::class)->execute();
    }
}
