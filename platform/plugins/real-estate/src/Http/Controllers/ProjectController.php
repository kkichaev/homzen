<?php

namespace Botble\RealEstate\Http\Controllers;

use Botble\Base\Events\BeforeEditContentEvent;
use Botble\Base\Events\CreatedContentEvent;
use Botble\Base\Events\UpdatedContentEvent;
use Botble\Base\Http\Actions\DeleteResourceAction;
use Botble\RealEstate\Facades\RealEstateHelper;
use Botble\RealEstate\Forms\ProjectForm;
use Botble\RealEstate\Http\Requests\ProjectRequest;
use Botble\RealEstate\Models\Account;
use Botble\RealEstate\Models\CustomFieldValue;
use Botble\RealEstate\Models\Project;
use Botble\RealEstate\Services\SaveFacilitiesService;
use Botble\RealEstate\Services\StoreProjectCategoryService;
use Botble\RealEstate\Tables\ProjectTable;
use Illuminate\Http\Request;

class ProjectController extends BaseController
{
    public function __construct()
    {
        parent::__construct();

        $this
            ->breadcrumb()
            ->add(trans('plugins/real-estate::project.name'), route('project.index'));
    }

    public function index(ProjectTable $dataTable)
    {
        $this->pageTitle(trans('plugins/real-estate::project.name'));

        return $dataTable->renderTable();
    }

    public function create()
    {
        $this->pageTitle(trans('plugins/real-estate::project.create'));

        return ProjectForm::create()->renderForm();
    }

    public function store(
        ProjectRequest $request,
        StoreProjectCategoryService $projectCategoryService,
        SaveFacilitiesService $saveFacilitiesService
    ) {
        $request->merge([
            'images' => array_filter($request->input('images', [])),
            'author_type' => Account::class,
        ]);

        /**
         * @var Project $project
         */
        $project = Project::query()->create($request->input());

        if (RealEstateHelper::isEnabledCustomFields()) {
            $this->saveCustomFields($project, $request->input('custom_fields', []));
        }

        $project->features()->sync($request->input('features', []));

        $saveFacilitiesService->execute($project, $request->input('facilities', []));

        $projectCategoryService->execute($request, $project);

        event(new CreatedContentEvent(PROJECT_MODULE_SCREEN_NAME, $request, $project));

        return $this
            ->httpResponse()
            ->setPreviousUrl(route('project.index'))
            ->setNextUrl(route('project.edit', $project->id))
            ->setMessage(trans('core/base::notices.create_success_message'));
    }

    public function edit(int|string $id, Request $request)
    {
        /**
         * @var Project $project
         */
        $project = Project::query()->findOrFail($id);

        $this->pageTitle(trans('plugins/real-estate::project.edit') . ' "' . $project->name . '"');

        event(new BeforeEditContentEvent($request, $project));

        return ProjectForm::createFromModel($project)->renderForm();
    }

    public function update(
        int|string $id,
        ProjectRequest $request,
        StoreProjectCategoryService $projectCategoryService,
        SaveFacilitiesService $saveFacilitiesService
    ) {
        /**
         * @var Project $project
         */
        $project = Project::query()->findOrFail($id);

        $request->merge([
            'images' => array_filter($request->input('images', [])),
            'author_type' => Account::class,
        ]);

        $project->fill($request->input());
        $project->save();

        if (RealEstateHelper::isEnabledCustomFields()) {
            $this->saveCustomFields($project, $request->input('custom_fields', []));
        }

        $project->features()->sync($request->input('features', []));

        $saveFacilitiesService->execute($project, $request->input('facilities', []));

        $projectCategoryService->execute($request, $project);

        event(new UpdatedContentEvent(PROJECT_MODULE_SCREEN_NAME, $request, $project));

        return $this
            ->httpResponse()
            ->setPreviousUrl(route('project.index'))
            ->setMessage(trans('core/base::notices.update_success_message'));
    }

    public function destroy(Project $project)
    {
        return DeleteResourceAction::make($project);
    }

    protected function saveCustomFields(Project $project, array $customFields = []): void
    {
        $customFields = CustomFieldValue::formatCustomFields($customFields);

        $project->customFields()
            ->whereNotIn('id', collect($customFields)->pluck('id')->all())
            ->delete();

        $project->customFields()->saveMany($customFields);
    }
}
