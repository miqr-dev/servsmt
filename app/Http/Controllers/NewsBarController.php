<?php

namespace App\Http\Controllers;

use App\NewsBar;
use Illuminate\Http\Request;

class NewsBarController extends Controller
{

    public function news_bar_check ()
    {
      $check = NewsBar::find(1)->get()->toArray();
      return $check;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\NewsBar  $newsBar
     * @return \Illuminate\Http\Response
     */
    public function show(NewsBar $newsBar)
    {
      $newsbar = NewsBar::find(1);
      return view('settings.publish.newsbar',compact('newsbar'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\NewsBar  $newsBar
     * @return \Illuminate\Http\Response
     */
    public function edit(NewsBar $newsBar)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\NewsBar  $newsBar
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, NewsBar $newsBar)
    {
          {
      $request->validate([
        'name' => 'required',
    ]);
        $newsbar = NewsBar::find(1);
        $newsbar -> name = $request -> name;
        $newsbar -> isNewsBar = $request -> isNewsBar;
        $newsbar ->update();
        return redirect()->route('home');
    }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\NewsBar  $newsBar
     * @return \Illuminate\Http\Response
     */
    public function destroy(NewsBar $newsBar)
    {
        //
    }
}
