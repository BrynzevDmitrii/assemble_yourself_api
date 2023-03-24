<?php

namespace App\Http\Controllers\Courses;

use App\Http\Controllers\Controller;
use App\Http\Requests\Courses\CreateRequest;
use App\Models\CourseModel;
use App\Models\User;
use App\QueryBuilders\CoursesQueryBuilder;
use Illuminate\Http\Request;

class CourseController extends Controller
{
    public function getCourses(CoursesQueryBuilder $coursesQueryBuilder)
    {

        $courses = $coursesQueryBuilder->getAll();

        return response()->json($courses);
    }

    public function getCourseById(CoursesQueryBuilder $coursesQueryBuilder, int $id)
    {

        $course = $coursesQueryBuilder->getCourseById($id);

        if (!isset($course)) {
            return response(['message' => 'Такого курса не существует'], 404);
        }
        return response()->json($course);
    }

    public function createCourse(CreateRequest $request)
    {
        $user = auth()->user();

        if (isset($user)) {

            $valid = $request->validated();
            $course = CourseModel::create([
                ...$valid,
                'author' => $user->getKey(),
            ]);

            if ($course) {
                return response(['message' => 'Success'], 200);
            }
            return response(['message' => 'Заполнены не все обязательные поля'], 400);
        }
        return response(['message' => 'Курс может составлять только авторизованный пользователь'], 401);
    }

    public function updateCourse(CreateRequest $request, CoursesQueryBuilder $coursesQueryBuilder, int $id)
    {
        $user = auth()->user();

        if (isset($user)) {
            $course = $coursesQueryBuilder->getCourseByIdWithAuthorId($id);
            if ($course) {

                if ($course['author'] === $user->getKey()) {
                    $valid = $request->validated();

                    if ($course->update([
                        ...$valid,
                        'author' => $user->getKey(),
                    ])) {
                        return response(['message' => 'Success'], 200);
                    }
                    return response(['message' => 'Заполнены не все обязательные поля'], 400);
                }
                return response(['message' => 'Описание курса может менять только автор курса'], 401);
            }

            return response(['message' => 'Такого курса не существует'], 404);
        }
        return response(['message' => 'Курс может менять только авторизованный пользователь'], 401);
    }
}
