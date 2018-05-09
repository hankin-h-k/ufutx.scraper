<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Sort;
use App\Models\Book;
use App\Models\Borrow;
use App\Models\ArkBook;
use App\Models\Library;
use App\Models\LibraryBook;

class HomeController extends Controller
{
    protected $library_id = 1;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //$this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('home');
    }


    //图书推荐
    public function bookRecommend(Request $request){
        $books = Book::with('book_libraries.library')->where('is_recommend', '>', 0)->orderBy('click', 'DESC')->orderBy('id', 'DESC')->paginate();
        return $this->success('book_recommend', $books);
    }

    //图书列表
    public function books(Request $request){
        $keyword = $request->get('keyword');
        $query = LibraryBook::where('library_id', $this->library_id)->orderBy('id', 'DESC')->with('book');
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

        return $this->success('books', $books);
    }

    //创建图书
    public function bookStore(Request $request){
        $isbn = $request->isbn;
        $book = Book::where('isbn', $isbn)->with('library_book')->first();
        if(!$book){
            $book = new Book;
            $library_book =  new LibraryBook;

            $book->isbn = $isbn; 
            $book->class_id = session('class_id')?session('class_id'):1; 
            $book->image = 'https://images.ufutx.com/201710/26/7cd9f274cd861fbb2ce30a99555f00bb.png'; 
            $book->save();

            $library_book->library_id = $this->library_id;
            $library_book->book_id = $book->id;
            $library_book->save();

        }

        if(!$book->title){
            $res = $this->getBookInfo($book);
            if($res){
                return $res;
            }
        }

        return $this->success('book', $book);
    }

    public function getBookInfo(&$book){
        $found = true;
        $douban_book = null;
        $result = null;

        $client = new \GuzzleHttp\Client();
        try{
            $res = $client->request('GET', 'https://api.douban.com/v2/book/isbn/'.$book->isbn);
            if($res->getStatusCode() != 200){
                \Log::debug('status code:'.$res->getStatusCode());
                \Log::debug($res->getBody());
                $found = false; 
            }
        }catch(\Exception $e){
            //服务器报错
            if($e->getCode()==400){
                ;//$result  = $this->failure('录入新书达150本/每小时，休息一下吧！');
            }
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
            $book->save();
        }else{
            //查找是否方舟旧书有记录
            $data = $this->getArkBook($book->isbn);

            foreach($data as $key=>$value){
                $book->$key = $value;
            }

            if(count($data)>0){
                $book->save();
            }
        }
        return $result;

    }

    /*
     * 获取方舟旧图书信息，转换成新的数据格式返回
     */
    private function getArkBook($isbn){
        $data = [];
        $arkbook = ArkBook::where('barcode', $isbn)->first();
        if($arkbook){
            if($arkbook->intro){
                $data['summary'] = $arkbook->intro;
            }  

            if($arkbook->name){
                $data['title'] = $arkbook->name;
            }  

            if($arkbook->author){
                $data['author'] = $arkbook->author;
            }  

            if($arkbook->price){
                $data['price'] = $arkbook->price;
            }  

        }
        return $data;
    }

    //图书信息
    public function book($id){
        $book = Book::with('library_book.sorts')->findOrFail($id);
        return $this->success('book', $book);
    }

    //图书详情
    public function bookDestory(Request $request, $id){
        $book = Book::with('library_book')->findOrFail($id);
        if($book){
            // LibraryBook::destroy($book->library_book->id);
            return $this->failure('好好的书，你为什么要删她呢？');
            //$book->delete();
        }
        return $this->success('delete');
    }

    //图书修改
    public function bookUpdate(Request $request, $id){
        $book = Book::with('library_book')->findOrFail($id);
        \Log::debug('class_id:'.session('class_id'));
        $cookie = null;

        if($request->isbn && $request->isbn != $book->isbn){
            $newbook = Book::where('isbn', $request->isbn)->first();
            if($newbook){
                if($newbook->title){
                    return $this->failure('ISBN编码已经存在');
                }
                $book->delete();
                $book = Book::with('library_book')->findOrFail($newbook->id);

            }
            $book->isbn = $request->isbn;
        }

        if($request->title && $request->title != $book->title){
            $book->title = $request->title;
        }

        if($request->class_id  && $request->class_id != $book->class_id){
            $book->class_id = $request->class_id;
            session(['class_id' => $book->class_id]);
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

        if($request->num && $request->num != $book->library_book->num){
            $book->library_book->num = $request->num;
            $book->library_book->save();
        }

        $book->save();

        return $this->success('book updateed', $book);
    }
}
