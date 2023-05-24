<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Client;
use TheSeer\Tokenizer\Exception;

class NewsController extends Controller
{
    // get news
    public function news(Request $request)
    {
        $page = $request->query('page', 1);
        // $per_page = $request->query('per_page', 20);
        $search = $request->query('search', '');
        $from = $request->query('from', '');
        $to = $request->query('to', '');
        $category = $request->query('category', '');
        $source = $request->query('source', '');

        $newsAPIData = $this->getFromNewsAPI([
            'page' => $page,
            'per_page' => 10,
            'search' => $search,
            'from' => $from,
            'to' => $to,
            'category' => $category,
            'source' => $source
        ]);

        $guardianData = $this->getFromTheGuardian([
            'page' => $page,
            'per_page' => 10,
            'search' => $search,
            'from' => $from,
            'to' => $to,
            'category' => $category,
            'source' => $source
        ]);

        $nyData = $this->getFromNYTimes([
            'page' => $page,
            'search' => $search,
            'from' => $from,
            'to' => $to,
            'category' => $category,
            'source' => $source
        ]);

        $data = array_merge($newsAPIData, $guardianData, $nyData);

        $compareNewsByPubDate = function ($a, $b) {
            return $a['pub_date'] < $b['pub_date'];
        };

        usort($data, $compareNewsByPubDate);

        return response()->json($data);
    }

    public function show($id)
    {

        return response()->json(['message' => 'Successfully logged out']);
    }

    protected function getFromNewsAPI($params)
    {
        $apiKey = env('NEWSAPI_TOKEN');
        $url = "https://newsapi.org/v2/everything";
        $reqParams = [
            'apiKey' => $apiKey,
            'page' => $params['page'],
            'pageSize' => $params['per_page'],
            'sortBy' => 'publishedAt',
            'q' => 'all'
        ];

        if (isset($params['category']) && $params['category'] !== "") {
            $category = array_filter(explode(" ", $params['category']));
            $reqParams['category'] = implode(",", $category);
            $url = "https://newsapi.org/v2/top-headlines";
        }
        else if (isset($params['source']) && $params['source'] !== "") {
            $source = array_filter(explode(" ", $params['source']));
            $reqParams['source'] = implode(",", $source);
            $url = "https://newsapi.org/v2/top-headlines";
        }

        if (isset($params['search']) && $params['search'] !== "") {
            $reqParams['q'] = $params['search'];
        }
        if (isset($params['from']) && $params['from'] !== "") {
            $reqParams['from'] = $params['from'];
        }
        if (isset($params['to']) && $params['to'] !== "") {
            $reqParams['to'] = $params['to'];
        }

        // Log::channel('stderr')->info($apiKey);
        $client = new Client([
            'verify' => false
        ]);

        $response = [];
        try {
            $response = $client->request('GET', $url, [
                'query' => $reqParams,
            ]);
        }
        catch (Exception $e) {
            return [];
        }
        $data = json_decode($response->getBody(), true);

        if ($response->getStatusCode() === 200 && $data['status'] === 'ok') {
            $res = $data['articles'];
            $articles = [];
            foreach ($res as $article) {
                $articles[] = [
                    "title" => isset($article['title']) ? $article['title'] : "",
                    "url" => isset($article['url']) ? $article['url'] : "",
                    "pub_date" => isset($article['publishedAt']) ? $article['publishedAt'] : "",
                    "author" => isset($article['author']) ? $article['author'] : "",
                    "category" => "",
                    "source" => isset($article["source"]["name"]) ? $article["source"]["name"] : ""
                ];
            }
            return $articles;
        }
        else {
            return [];
        }
    }

    protected function getFromTheGuardian($params)
    {
        $apiKey = env('GUARDIAN_TOKEN');
        $url = "https://content.guardianapis.com/search";
        $reqParams = [
            'api-key' => $apiKey,
            'page' => $params['page'],
            'page-size' => $params['per_page'],
            'order-by' => 'newest',
        ];

        if (isset($params['category']) && $params['category'] !== "") {
            $category = array_filter(explode(" ", $params['category']));
            $reqParams['section'] = implode(",", $category);
        }
        else if (isset($params['source']) && $params['source'] !== "") {
            $source = array_filter(explode(" ", $params['source']));
            $reqParams['tag'] = implode(",", $source);
        }

        if (isset($params['search']) && $params['search'] !== "") {
            $reqParams['q'] = $params['search'];
        }
        if (isset($params['from']) && $params['from'] !== "") {
            $reqParams['from-date'] = $params['from'];
        }
        if (isset($params['to']) && $params['to'] !== "") {
            $reqParams['to-date'] = $params['to'];
        }

        $client = new Client([
            'verify' => false
        ]);
        $response = [];
        try {
            $response = $client->request('GET', $url, [
                'query' => $reqParams,
            ]);
        }
        catch (Exception $e) {
            return [];
        }

        $data = json_decode($response->getBody(), true);
        $data = $data['response'];

        if ($response->getStatusCode() === 200 && $data['status'] === 'ok') {
            $res = $data['results'];
            $articles = [];
            foreach ($res as $article) {
                $articles[] = [
                    "title" => isset($article['webTitle']) ? $article['webTitle'] : "",
                    "url" => isset($article['webUrl']) ? $article['webUrl'] : "",
                    "pub_date" => isset($article['webPublicationDate']) ? $article['webPublicationDate'] : "",
                    "author" => "",
                    "category" => isset($article['sectionName']) ? $article['sectionName'] : "",
                    "source" => "",
                ];
            }
            return $articles;
        }
        else {
            return [];
        }
    }

    protected function getFromNYTimes($params)
    {
        $apiKey = env('NYTIMES_TOKEN');
        $url = "https://api.nytimes.com/svc/search/v2/articlesearch.json";
        $reqParams = [
            'api-key' => $apiKey,
            'sort' => 'newest',
            'page' => $params['page']
        ];

        if (isset($params['category']) && $params['category'] !== "") {
            $reqParams['fq'] = `section_name:{$params['category']}`;
        }
        else if (isset($params['source']) && $params['source'] !== "") {
            $source = array_filter(explode(" ", $params['source']));
            $source = "\"" . implode("\" \"", $source) . "\"";
            $reqParams['fq'] = `source:({$source})`;
        }

        if (isset($params['search']) && $params['search'] !== "") {
            $reqParams['q'] = $params['search'];
        }
        if (isset($params['from']) && $params['from'] !== "") {
            $reqParams['begin_date'] = $params['from'];
        }
        if (isset($params['to']) && $params['to'] !== "") {
            $reqParams['end_date'] = $params['to'];
        }

        $client = new Client([
            'verify' => false
        ]);
        $response = [];
        try {
            $response = $client->request('GET', $url, [
                'query' => $reqParams,
            ]);
        }
        catch (Exception $e) {
            return [];
        }

        $data = json_decode($response->getBody(), true);

        if ($response->getStatusCode() === 200 && $data['status'] === 'OK') {
            $res = $data['response']['docs'];
            $articles = [];
            foreach ($res as $article) {
                $author = "";
                if (isset($article['byline']['original'])) {
                    $author = $article['byline']['original'];
                }
                $articles[] = [
                    "title" => isset($article['headline']['main']) ? $article['headline']['main'] : "",
                    "url" => isset($article['web_url']) ? $article['web_url'] : "",
                    "pub_date" => isset($article['pub_date']) ? $article['pub_date'] : "",
                    "author" => isset($article['byline']['original']) ? $article['byline']['original'] : "",
                    "category" => isset($article['section_name']) ? $article['section_name'] : "",
                    "source" => isset($article["source"]) ? $article["source"] : ""
                ];
            }
            return $articles;
        }
        else {
            return [];
        }
    }
}
