<?php

namespace App\Http\Controllers;

use App\Models\Sort;
use App\Models\Library;
use Illuminate\Http\Request;

class SortController extends LibraryController
{
    protected $library_id = 1;

    //图书分类
    public function librarySorts($library_id){
        $sorts = Sort::where('library_id', $library_id)->get();
        $library = Library::find($library_id);
        return $this->success('sorts', compact('sorts', 'library'));
    }

    //图书分类
    public function item($id){
        $sort = Sort::find($id);

        return $this->success('sort', $sort);
    }

    //图书分类
    public function update(Request $request, $id){
        $sort = Sort::find($id);

        // todo: check right and library_id

        if($request->name && $request->name != $sort->name){
            $sort->name = $request->name;
            if(Sort::where(['name'=>$sort->name, 'library_id'=>$this->library_id])->count()){
                return $this->failure('分类名称已经存在');
            }
        }

        if($request->intro && $request->intro != $sort->intro) {
            $sort->intro = $request->intro ;
        }


        $sort->save();


        return $this->success('sort update', $sort);
    }

    public function librarySortstore(Request $request, $library_id){
        $sort = new Sort;
        $sort->name = $request->name;
        $sort->library_id = $library_id;
        $sort->intro = $request->intro ;

        if(!$sort->intro){
            return $this->failure('简介不能为空');
        }

        if(Sort::where(['name'=>$sort->name, 'library_id'=>$library_id])->count()){
            return $this->failure('分类名称已经存在');
        }

        if(!$sort->name){
            return $this->failure('名称不能为空');
        }

        
        $sort->save();

        return $this->success('sort store', $sort);
    }

    public function destory($id){
        $sort = Sort::find($id);

        //todo: check right

        if($sort){
            $sort->delete();
            //todo: remove sort_id of library_book
        }

        return $this->success('sort');
    }

}
