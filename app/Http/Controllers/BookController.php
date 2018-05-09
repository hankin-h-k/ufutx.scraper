<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Book;
use App\Models\Sort;
use App\Models\Member;
use App\Models\Borrow;
use App\Models\LibraryBook;
use App\Models\Library;


use Carbon\Carbon;
use Illuminate\Http\Request;

class BookController extends LibraryController
{
    protected $library_id = 1;

    //图书
    public function books(Request $request, $library_id=0, $sort_id=0){
        if(!$library_id){
            $library_id = $request->input('library_id'); 
        }

        $keyword = $request->get('keyword');
        $query = Book::orderBy('id', 'DESC');

        if($library_id){
            if($sort_id){
                $ids = LibraryBook::where(['library_id'=>$library_id, 'sort_id'=>$sort_id])->pluck('book_id');
            }else{
                $ids = LibraryBook::where('library_id', $library_id)->pluck('book_id');
            }
            $query = $query->whereIn('id', $ids);
        }


        if($keyword){
            $query = $query->where(function($qr) use($keyword){
            $qr->where('title', 'LIKE', '%'.$keyword.'%')
            ->orWhere('origin_title', 'LIKE', '%'.$keyword.'%')
            ->orWhere('author', 'LIKE', '%'.$keyword.'%')
            ->orWhere('publisher', 'LIKE', '%'.$keyword.'%')
            ->orWhere('summary', 'LIKE', '%'.$keyword.'%')
            ->orWhere('translator', 'LIKE', '%'.$keyword.'%');
            });
        }
        $books = $query->paginate();

        return $this->success('library\'s books', $books);
    }

    //
    public function libraryBooks(Request $request, $library_id){
        return $this->books($request, $library_id);
    }

    public function librarySortBooks(Request $request, $library_id, $sort_id){
        return $this->books($request, $library_id, $sort_id);
    }

    //图书馆书籍
    public function libraryBook($library_id, $id){
        $book = Book::with('borrows.user.wechat')->find($id);
        $book->library = Library::find($library_id);
        $book->library_book = LibraryBook::where(['library_id'=>$library_id, 'book_id'=>$id])->first();
        $book->sort = Sort::find($book->library_book->sort_id);
        $book->sorts = Sort::where('library_id', $library_id)->get();
        $book->image = str_replace('mpic', 'lpic', $book->image);
        $book->member = Member::where(['library_id'=>$library_id, 'user_id'=>auth()->id()])->first();
        $book->comments = $book->comments()->with('user.wechat')->limit(5)->get();
        return $this->success('library_book', $book);
    }

    //图书
    public function item($id){
        $book = Book::with('borrows.user.wechat', 'book_libraries.library', 'sort', 'library_book.sorts')->find($id);
        if(count($book->book_libraries)){
            foreach ($book->book_libraries as $book_library) {
                $member = Member::where(['user_id'=>\Auth::id(), 'library_id'=>$book_library->library_id])->first();
                $book_library->member = $member;
            }
        }
        $book->click +=1;
        $book->image = str_replace('mpic', 'lpic', $book->image);
        $book->save();
        $book->comments = $book->comments()->with('user.wechat')->limit(5)->get();
        return $this->success('book', $book);
    }

    /**
     * 图书二
     */
    public function itemV2(Request $request, $id)
    {       
        $book = Book::with('borrows.user.wechat', 'sort', 'library_book.sorts')->find($id);
        $book_library = null;
        // dd($request->library_id);
        if ($request->has('library_id') && $request->library_id) {
            $book_libraries = LibraryBook::with('library')->where('book_id', $id)->where('library_id', '<>', $request->library_id)->get();
            $book_library = LibraryBook::with('library')->where('book_id', $id)->where('library_id', $request->library_id)->first();
            if($book_library){
                $member = Member::where(['user_id'=>\Auth::id(), 'library_id'=>$book_library->library_id])->first();
                $book_library->member = $member;
            }
            
        }else{
            $book_libraries = LibraryBook::with('library')->where('book_id', $id)->get();
        }
        $book->click +=1;
        $book->image = str_replace('mpic', 'lpic', $book->image);
        $book->save();
        $book->book_library = $book_library;
        if(count($book_libraries)){
            foreach ($book_libraries as $book_library) {
                $member = Member::where(['user_id'=>\Auth::id(), 'library_id'=>$book_library->library_id])->first();
                $book_library->member = $member;
            }
        }
        $book->book_libraries = $book_libraries;
        $book->comments = $book->comments()->with('user.wechat')->limit(5)->get();
        return $this->success('book', $book);
    }



    //图书
    public function update(Request $request, $id){
        $book = Book::find($id);

        if($request->name && $request->name != $book->name){
            $book->name = $request->name;
            if(Book::where(['name'=>$book->name, 'library_id'=>$this->library_id])->count()){
                return $this->failure('分类名称已经存在');
            }
        }

        if($request->intro && $request->intro != $book->intro) {
            $book->intro = $request->intro ;
        }

        $book->save();

        return $this->success('book update', $book);
    }

    /*
     * 保存图书
     */
    public function store(Request $request){
        $book = new Book;
        $book->name = $request->name;
        $book->library_id = $this->library_id;
        $book->intro = $request->intro ;

        if(!$book->intro){
            return $this->failure('简介不能为空');
        }

        if(Book::where(['name'=>$book->name, 'library_id'=>$this->library_id])->count()){
            return $this->failure('分类名称已经存在');
        }

        if(!$book->name){
            return $this->failure('名称不能为空');
        }

        $book->save();

        return $this->success('book store', $book);
    }

    /*
     * 删除图书
     */
    public function destory($id){
        $book = Book::find($id);

        if($book){
            ;//$book->delete();
        }

        return $this->success('book');
    }

    /*
     * 图书评论
     */
    public function comments($id){
        $book = Book::findOrFail($id); 
        return $this->success('book_comments', $book->comments()->with('user.wechat')->paginate());
    }

    /*
     * 发布图书评论
     */
    public function commentStore(Request $request, $id){
        $user = auth()->user();
        $book = Book::findOrFail($id);
        $content = $request->input('content');
        $comment = $user->comment($book, $content); 
        return $this->success('book_comments', $comment);
    }

    /*
     * 图书评论详情
     */
    public function comment(Request $request, $id, $comment_id){
        $book = Book::findOrFail($id); 
        $comment = $book->comments()->with('user.wechat')->where('id', $comment_id)->first(); 
        return $this->success('book_comment', $comment);
    }

    /*
     * 删除图书评论
     */
    public function commentDestory(Request $request, $id, $comment_id){
        $user = auth()->user();
        $book = Book::findOrFail($id); 
        $comment = $book->comments()->with('user')->where('id', $comment_id)->first();
        if($comment->user->id == $user->id){
            $comment = $book->comments()->where('id', $comment_id)->delete();
            return $this->success('book_comment_destroy', $comment);
        }else{
            return $this->failure('book_comment_destroy_no_access', $comment);
        }
    }

    //创建图书
    public function libraryBookStore(Request $request, $library_id){

        //todo: check library_id is Admin
        $res = $this->bookStore($request);
        if ($res == 'false') {
            return $res;
        }
        $book = $res->original['data'];
        if(!$book){
            $book = new Book;
            $book->isbn = $isbn; 
            $book->class_id = session('class_id')?session('class_id'):1; 
            $book->image = 'https://images.ufutx.com/201710/26/7cd9f274cd861fbb2ce30a99555f00bb.png'; 
            $book->save();
        }

        $library_book_id = LibraryBook::where(['book_id'=>$book->id, 'library_id'=>$library_id])->value('id');

        if($library_book_id){
            $library_book =  LibraryBook::find($library_book_id);
        }else{
            $library_book =  new LibraryBook;
            $library_book->library_id = $library_id;
            $library_book->book_id = $book->id;
            $library_book->save();
        }

        $book->sorts = Sort::where('library_id', $library_id)->get();
        $book->library_book = $library_book;

        return $this->success('book', $book);
    }

    public function bookStore(Request $request){
        $isbn = $request->isbn;
        if (empty($isbn)) {
            return 'false';
        }
        $book = Book::where('isbn', $isbn)->first();
        if(!$book){
            $res = $this->getBookInfo($isbn);
            $result = $res['result'];
            if($result){
                //todo：是否在查询之后？
                if($request->title){
                    $book =  new Book;
                    $book->title = $request->title;
                    //$book->origin_title = $douban_book->origin_title;
                    $book->author = $request->author;
                    //$book->translator = implode(' ', $douban_book->translator);
                    $book->image = $request->image;
                    $book->summary = $request->summary;
                    //$book->publisher= $douban_book->publisher;
                    //$book->price = (float)$douban_book->price;
                    //$book->pubdate = $douban_book->pubdate;
                    //$book->pages  = (int)$douban_book->pages;
                    $book->class_id = 0;
                    $book->isbn =  $isbn;
                    $book->save();
                }else{
                    //标准json接口返回
                    return 'false';
                    return $result->getContent();
                }
            }else{
                $book =  new Book;
                foreach($res['book'] as $key=>$value){
                    $book->$key =  $value;
                }
                $book->isbn =  $isbn;
                $book->save();
            }
        }

        return $this->success('book', $book);
    }

    // get book info from douban
    public function getBookInfo($isbn){
        $book = (object)['isbn'=>$isbn];
        $found = true;
        $douban_book = null;
        $result = null;

        $client = new \GuzzleHttp\Client();
        try{
            $res = $client->request('GET', 'https://api.douban.com/v2/book/isbn/'.$isbn);
            if($res->getStatusCode() != 200){
                $result  = $this->failure($res->getStatusCode());
                \Log::debug('status code:'.$res->getStatusCode());
                \Log::debug($res->getBody());
                $found = false; 
            }
        }catch(\Exception $e){
            //服务器报错
            //if($e->getCode()==400){
            //}
            $result  = $this->failure($e->getMessage());
            \Log::debug($e->getMessage());
            $found = false;
        }

        $found && ($douban_book = json_decode($res->getBody()));
        if($douban_book){
            //
            $book->title = $douban_book->title.($douban_book->subtitle?':'.$douban_book->subtitle:'');
            $book->origin_title = $douban_book->origin_title;
            $book->author = implode(' ', $douban_book->author);
            $book->translator = implode(' ', $douban_book->translator);
            $book->image = $douban_book->image;
            $book->summary = $douban_book->summary;
            $book->publisher= $douban_book->publisher;
            $book->price = (float)$douban_book->price;
            $book->pubdate = $douban_book->pubdate;
            $book->pages  = (int)$douban_book->pages;
            $book->class_id = 0;
        }
        return compact('book', 'result');
    }


    //图书馆图书修改
    public function libraryBookUpdate(Request $request, $library_id, $id){
        $book = Book::findOrFail($id);
        $library_book_id = LibraryBook::where(['library_id' => $library_id, 'book_id'=>$id])->value('id');
        $library_book = LibraryBook::find($library_book_id);
        \Log::debug('class_id:'.session('class_id'));
        $cookie = null;

        if($request->title && $request->title != $book->title){
            $book->title = $request->title;
        }
    
        if($request->class_id  && $request->class_id != $library_book->sort_id){
            // todo:check sort_id is validate
            $library_book->sort_id = $request->class_id;
            $library_book->save();
            session(['class_id' => $library_book->sort_id]);
        }

        if($request->origin_title && $request->origin_title  != $book->origin_title ){
            $book->origin_title  = $request->origin_title ;
        }

        if($request->author && $request->author != $book->author){
            $book->author = $request->author;
        }

        if($request->translator && $request->translator != $book->translator){
            $book->translator = $request->translator;
        }

        if($request->summary && $request->summary != $book->summary){
            $book->summary = $request->summary;
        }

        if($request->publisher && $request->publisher != $book->publisher){
            $book->publisher = $request->publisher;
        }

        if($request->price && $request->price != $book->price){
            $book->price = $request->price;
        }

        if($request->image && $request->image != $book->image){
            $book->image = $request->image;
        }

        if($request->pubdate && $request->pubdate != $book->pubdate){
            $book->pubdate = $request->pubdate;
        }

        if($request->pages && $request->pages != $book->pages){
            $book->pages = (int)$request->pages;
        }

        if($request->intro && $request->intro != $book->intro){
            $book->intro = (int)$request->intro;
        }

        if($request->num && $request->num != $library_book->num){
            $library_book->num = $request->num;
            $library_book->save();
        }

        $book->save();

        return $this->success('library book updateed', $book);
    }

    /**
     * 删除图书馆图书
     */
    public function libraryBookDelete($library_id, $id){
        $library_book = LibraryBook::where(['library_id' => $library_id, 'book_id'=>$id])->first();
        if ($library_book) {
            //是否该车在借阅
            $borrow_count = Borrow::where(['library_id' => $library_id, 'book_id'=>$id, 'status'=>'BORROW'])->count();
            if ($borrow_count) {
                return $this->failure('该图书还在借阅中');
            }else{
                $library_book->delete();
                return $this->success('ok');
            }
            
        }else{
            return $this->failure('该图书不存在');
        }
    }

    public function userBooks(Request $request, $user_id=0){

        if($user_id){
            $user = User::find($user_id);
        }else{
            $user =  auth()->user();
        }
        $library =  $this->getMylibrary($user->id);
        $library_id = $library->id;
        $keyword = $request->get('keyword');
        //$query = Book::with('library_book')->OrderBy('id');

        $query = LibraryBook::where('library_id', $library_id)->with('book')->orderBy('id', 'DESC');

        if($keyword){
            $query = $query->whereHas('book', function($qr) use($keyword){
            $qr->where('title', 'LIKE', '%'.$keyword.'%')
            ->orWhere('origin_title', 'LIKE', '%'.$keyword.'%')
            ->orWhere('author', 'LIKE', '%'.$keyword.'%')
            ->orWhere('publisher', 'LIKE', '%'.$keyword.'%')
            ->orWhere('summary', 'LIKE', '%'.$keyword.'%')
            ->orWhere('translator', 'LIKE', '%'.$keyword.'%');
            });
        }
        $books = $query->paginate();

        return $this->success('user library\'s books', $books);
    }

    //添加图书到我的收藏
    public function userBooksStore(Request $request){
        $ids= $request->ids;
        $library = $this->getMyLibrary();
        
        foreach($ids as $id){
            $book = Book::findOrFail($id);
            $library_book_id = LibraryBook::where(['book_id'=>$book->id, 'library_id'=>$library->id])->value('id'); 
            if(!$library_book_id){
                $library_book =  new LibraryBook;
                $library_book->library_id = $library->id;
                $library_book->book_id = $id;
                $library_book->save();
            }
        }

        return $this->success('books_stored', $library);
    }

    //添加图书到我的图书馆
    public function libraryBooksStore(Request $request, $id){
        // $books= json_decode($request->books);
        $books= ($request->books);
        $library = Library::findOrFail($id);

        foreach($books as $item){
            $id = $item['id'];
            if(!array_key_exists('sort_id', $item) || $item['sort_id']==''){
                $item['sort_id'] = 0;
            }
            $book = Book::findOrFail($id);
            $library_book_id = LibraryBook::where(['book_id'=>$book->id, 'library_id'=>$library->id])->value('id'); 
            if(!$library_book_id){
                $library_book =  new LibraryBook;
                $library_book->library_id = $library->id;
                $library_book->book_id = $id;
                $library_book->sort_id = $item['sort_id'];
                $library_book->save();
            }else{
                $library_book =  LibraryBook::find($library_book_id);
                $library_book->sort_id = $item['sort_id'];
                $library_book->save();
            }
        }

        return $this->success('books_stored', $library);
    }

    //手动添加图书
    public function bookStoreV2(Request $request)
    {
        $title = $request->title;
        $origin_title = $request->origin_title;
        $author = $request->author;
        $translator = $request->translator;
        $image = $request->image;
        $summary = $request->summary;
        $publisher = $request->publisher;
        $price = (float)$request->price;
        $pubdate = $request->pubdate;
        $pages  = (int)$request->pages;
        $class_id = $request->input('class_id',0);
        $isbn = $request->input('isbn');
        if ($isbn) {
            $book = Book::where('isbn', $isbn)->first();
            if(!empty($book)){
                $this->bookAddLibrary($book->id);
                return $this->success('收藏成功');    
            }
        }else{
            $isbn =  $this->getIsbn();
        }
        $book = Book::create([
            'title'=>$title,
            'origin_title'=>$origin_title,
            'author'=>$author,
            'translator'=>$translator,
            'image'=>$image,
            'summary'=>$summary,
            'publisher'=>$publisher,
            'price'=>$price,
            'pubdate'=>$pubdate,
            'pages'=>$pages,
            'class_id'=>$class_id,
            'isbn'=>$isbn,
        ]);
        $this->bookAddLibrary($book->id);
        return $this->success('收藏成功', $book);
    }

    public function bookAddLibrary($book_id)
    {
        $library = $this->getMyLibrary();
        $library_book_id = LibraryBook::where(['book_id'=>$book_id, 'library_id'=>$library->id])->value('id'); 
        if(!$library_book_id){
            $library_book =  new LibraryBook;
            $library_book->library_id = $library->id;
            $library_book->book_id = $book_id;
            $library_book->sort_id = 0;
            $library_book->save();
        }
        // else{
        //     $library_book =  LibraryBook::find($library_book_id);
        //     $library_book->sort_id = 0;
        //     $library_book->save();
        // }
        return ;
    }

    public function getIsbn()
    {
        $dateline = time();
        $mix = rand(10, 99);
        return 'u'.$dateline . $mix;
    }

}
