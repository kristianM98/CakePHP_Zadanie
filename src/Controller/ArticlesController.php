<?php

namespace App\Controller;

use App\Controller\AppController;
use App\Model\Entity\Article;
use Cake\Database\Connection;
use Cake\Datasource\ConnectionManager;
use Cake\Error\Debugger;
use Cake\I18n\FrozenTime;
use Cake\ORM\Locator\LocatorAwareTrait;
use function PHPUnit\Framework\returnArgument;

class ArticlesController extends AppController
{
    public function initialize(): void
    {
        parent::initialize();

        $this->loadComponent('Paginator');
        $this->loadComponent('Flash'); // Include the FlashComponent
    }

    public function index()
    {
        $articles = $this->Paginator->paginate($this->Articles->find());
        $this->set(compact('articles'));
    }

    public function view($slug)
    {
        $article = $this->Articles->findBySlug($slug)->firstOrFail();
        $this->set(compact('article'));
    }

    public function add()
    {
        $article = $this->Articles->newEmptyEntity();
        if ($this->request->is('post')) {
            $article = $this->Articles->patchEntity($article, $this->request->getData());

            // Hardcoding the user_id is temporary, and will be removed later
            // when we build authentication out.
            $article->user_id = 1;

            if ($this->Articles->save($article)) {
                $this->Flash->success(__('Your article has been saved.'));
                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('Unable to add your article.'));
        }
        $this->set('article', $article);
    }

    public function edit($slug)
    {
        $article = $this->Articles
            ->findBySlug($slug)
            ->firstOrFail();

        $title = $article->get('title'); //get 'title' from table
        if ($this->request->is(['post', 'put'])) {
            $this->Articles->patchEntity($article, $this->request->getData());
            if ($title != $article->get('title')) {   // checked if 'title' is changed
                $article->title_edited = FrozenTime::now();     // added date
            }
            if ($this->Articles->save($article)) {
               $this->Flash->success(__('Your article has been updated.'));
                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('Unable to update your article.'));
        }

            $this->set('article', $article);
        }


    public function delete($slug)
    {
        $this->request->allowMethod(['post', 'delete']);

        $article = $this->Articles->findBySlug($slug)->firstOrFail();
        if ($this->Articles->delete($article)) {
            $this->Flash->success(__('The {0} article has been deleted.', $article->title));
            return $this->redirect(['action' => 'index']);
        }
    }

    public function updateTitle($title)
    {
//        $db =& ConnectionManager::getDataSource($this->useDBConfig);
//        return $db->LastAffected($title);
//        $title = $articles->get($title);

//        $articles = $this->getTableLocator()->get('Articles');
//
//        $query = $this->Articles->find();

        $articles = $this->getTableLocator()->get('articles');
        $title = $articles->get('title');



        $query = $articles->find();     // Find a 'title'
        $query->select([
                'title' => $title
            ]);

        $query = $articles->query();
        $query->insert(['title_edited'])
            ->values([
                'title_edited' => Now()
            ])
            ->execute();
        $article->insert(['title_edited'])    //insert value of actual datetime in 'title_edited'
        ->values([
            'title_edited' => Now()
        ]);


        if ($title != $article->get('title')->value) {   // checked if 'title' is changed
            $articles = $this->getTableLocator()->get('articles');
            $query = $articles->query();
            $query->insert(['title_edited'])
                ->values([
                    'title_edited' => FrozenTime::now()
                ])
                ->execute();


            $articles = $this->getTableLocator()->get('articles');
            $query = $articles->query();
            $query->insert(['title_edited'])
                ->values([
                    'title_edited' => FrozenTime::now()
                ])
                ->execute();
        }
    }

}
