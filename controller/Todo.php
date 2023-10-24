<?php

class CI_Todo extends Controller
{
    public string|ModelTodo $model = "ModelTodo";

    function demo()
    {
        $params = array_merge([
            'reset_vocabulary' => true,
            'reset' => true
        ], GET());

        if ($params['reset_vocabulary']) {

            ModelVocabulary::delete([
                'context' => ['todo-priority', 'todo-status']
            ]);


            ModelVocabulary::insert(gen_voc('todo-status', [
                "Not Started", "In Progress", "Completed", "On Hold"
            ]));
            ModelVocabulary::insert(gen_voc('todo-priority', [
                "Low", "Medium", "High"
            ]));
        }
        if ($params['reset']) {
            $this->model::delete();
        }
        $folder = str_replace('CI_', '', __class__);
        $res = file_get_contents(DIR . '/model/' . $folder . '/demo.json');
        return $this->model::insert(json_decode($res, true));
    }
}
