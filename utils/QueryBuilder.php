<?php

class QueryBuilder {
    private $selects;
    private $from;
    private $innerJoins;
    private $leftJoins;
    private $rightJoins;
    private $andJoins;
    private $orJoins;
    private $where;
    private $tablesJoin;
    private $andWheres;
    private $orWheres;
    private $groupBy;
    private $andGroupBy;
    private $having;    
    private $andHaving;   
    private $orderBys;
    private $limits;
    private $offsets;
    private $nestedQuery;
    
    public function __construct($from) {
        $this->setFrom($from);
        $this->setAndWheres(array());
        $this->setSelects(array());
        $this->setInnerJoins(array());
        $this->setLeftJoins(array());
        $this->setTablesJoin(array());
    }
    
    public function select($select) {
        $this->addSelect($select);
        return $this;
    }
    
    public function where($where) {    
        if($this->getWhere()==''){
            $this->setWhere($where);
        }
        else {
            $this->addAndWhere($where);
        }
        return $this;
    }
    
    public function andWhere($andWhere) {        
        if($this->getWhere() == "") {
            $this->setWhere($andWhere);
        }
        else {
            $this->addAndWhere($andWhere);
        }
        return $this;
    }
    
    public function orWhere($orWhere) {
        if($this->getWhere() ==  "") {
            trigger_error("Vous devez déclarer un where avant de remplir une clause where de type OR !");
            return;
        }
        else {
            $this->addOrWhere($orWhere);
            return $this;
        }
    }
    
    public function join($join, $fieldJoin, $alias = "") {
        $this->innerJoin($join, $fieldJoin, $alias);
    }

    public function innerJoin($join, $fieldJoin, $alias = "") {
        $this->addInnerJoin($this->formatJoin($join, $fieldJoin, $alias));
    }
    
    public function leftJoin($join, $fieldJoin, $alias = "") {
        $this->addLeftJoin($this->formatJoin($join, $fieldJoin, $alias));
    }
    
    public function rightJoin($join, $fieldJoin, $alias = "") {
        $this->addRightJoin($this->formatJoin($join, $fieldJoin, $alias));
    }
    
    public function andInnerJoin($where) {
        if($this->getInnerJoins() == "") {
            trigger_error("Erreur lors de l'ajout d'une clause AND dans la jointure ! Détail : Aucun Inner join n'est défini ! ");
            return;
        }
        else {
            $this->addAndInnerJoin($where);
        }
        return $this;
    }
    
    public function formatJoin($join, $fieldJoin, $alias = "") {
        if($fieldJoin === ""){
            trigger_error('Le nom du champs doit être renseigné !');
            return;
        }
        $tableArray = $this->tableJoinToArray($join);
        $this->addTablesJoin($tableArray['foreign']);
        print_r($this->getTablesJoin());
        if($alias === "") {
            $alias = $tableArray['foreign'];
        }
        return $tableArray['foreign']." ".$alias." ON ".$alias.".".$fieldJoin." = ".$tableArray['primary'].".".$fieldJoin;
    }
    
    // Transforme en array, une chaine de la forme "table1.table2" (.table2 est facultatif)
    public function tableJoinToArray($join) {
        $primary = "";
        $arrayTable = array_filter(explode(".", trim($join)));
        if($arrayTable[0] == "" && $arrayTable[1] == "") {
            trigger_error("Erreur lors de la création de la requete ! Détail: Aucune tables de jointure n'est renseignées !");
            return;
        }
        foreach($arrayTable AS $table) {
            if(trim($table) === $this->getFrom()) {
                $primary = trim($table);
            }
            if(array_search(trim($table), $this->getTablesJoin()) != false) {
                $primary = trim($table);
            }
            else {
                $foreign = trim($table);
            }
        }
        if($primary === "") {
            $primary = $this->getFrom();
        }
        return array("primary" => $primary, "foreign" => $foreign);
    }
    
    public function getQuery() {
        return  $this->getStringSelects().
                $this->getStringFrom().
                $this->getStringJoins().
                $this->getStringWhere().
                $this->getStringAndWheres()
                // $this->getStringsOrderBys()
        ;
    }
    
    public function getStringSelects() {
        return "SELECT ".implode(",\n", $this->getSelects());
    }
    
    public function getStringFrom() {
        return "\nFROM ".$this->getFrom();
    }
    
    public function getStringWhere() {
        if(!is_null($this->getWhere())){
            return "\nWHERE ".$this->getWhere();
        }
    }
    public function getStringAndWheres() {
        if(count($this->getAndWheres()) > 0 ){
            return "\nAND ".implode("\nAND ", $this->getAndWheres());
        }
    }
    
    public function getStringJoins() {
        if(count($this->getInnerJoins()) > 0 ){
            return "\nINNER JOIN ".implode("\nINNER JOIN ", $this->getInnerJoins());
        }
        if(count($this->getLeftJoins()) > 0 ){
            return "\nLEFT JOIN ".implode("\nLEFT JOIN ", $this->getLeftJoins());
        }
        if(count($this->getRightJoins()) > 0 ){
            return "\nRIGHT JOIN ".implode("\nRIGHT JOIN ", $this->getRightJoins());
        }
    }
    
    /*****************************   GETTERS   ********************************/
    /*****************************      &      ********************************/
    /*****************************   SETTERS   ********************************/        
    
    public function getSelects() {
        return $this->selects;
    }
    public function setSelects(array $selects) {
        $this->selects = $selects;
    }
    public function addSelect($select) {
        $this->selects[] = $select;
    }    
    public function getFrom() {
        return $this->from;
    }
    public function setFrom($from) {
        if(!is_string($from)){
            trigger_error("Le nom de la table doit être un string !");
            return;
        }
        if($from === "") {
            trigger_error("Vous devez déclarer un nom de table dans votre requete !");
            return;
        }
        $this->from = $from;
    }        
    public function getWhere(){
        return $this->where;
    }
    public function setWhere($where){
        $this->where = $where;
    }
    public function getAndWheres(){
        return $this->andWheres;
    }
    public function setAndWheres(array $andWheres){
        $this->andWheres = $andWheres;
    }
    public function addAndWhere($andWhere){
        $this->andWheres[] = $andWhere;
    }    
    public function getOrWheres(){
        return $this->orWheres;
    }
    public function setOrWheres(array $orWheres){
        $this->orWheres = $orWheres;
    }
    public function setTablesJoin($tableJoin) {
        $this->tablesJoin = $tableJoin;
    }
    public function addTablesJoin($tableJoin){
        $this->tablesJoin[] = $tableJoin;
    }    
    public function getTablesJoin(){
        return $this->tablesJoin;
    }
    public function getInnerJoins(){
        return $this->innerJoins;
    }
    public function setInnerJoins($innerJoins){
        $this->innerJoins = $innerJoins;
    }    
    public function addInnerJoin($innerJoin){
        $this->innerJoins[] = $innerJoin;
    }    
    public function getLeftJoins(){
        return $this->leftJoins;
    }
    public function setLeftJoins($leftJoins){
        $this->leftJoins = $leftJoins;
    }    
    public function addLeftJoin($leftJoin){
        $this->leftJoins[] = $leftJoin;
    }    
    public function getRightJoins(){
        return $this->rightJoins;
    }
    public function setRightJoins($rightJoins){
        $this->rightJoins = $rightJoins;
    }    
    public function addRightJoin($rightJoin){
        $this->rightJoins[] = $rightJoin;
    }    
    public function getAndJoins(){
        return $this->andJoins;
    }
    public function setAndJoins($andJoins){
        $this->andJoin = $andJoins;
    }
    public function addAndJoin($andJoin){
        $this->andJoins[] = $andJoin;
    } 
    public function getOrJoins(){
        return $this->orJoin;
    }
    public function setOrJoins($orJoin){
        $this->orJoin = $orJoin;
    }
    public function getLimit(){
        return $this->limit;
    }
    public function setLimit($limit){
        $this->limit = $limit;
    }    
    public function getOffset(){
        return $this->offset;
    }
    public function setOffset($offset){
        $this->offset = $offset;
    }    
    public function getOrderBy(){
        return $this->orderBy;
    }
    public function setOrderBy($orderBy){
        $this->orderBy = $orderBy;
    }    
    public function getNestedQuery(){
        return $this->nestedQuery;
    }
    public function setNestedQuery($nestedQuery){
        $this->nestedQuery = $nestedQuery;
    }    
    public function getHaving(){
        return $this->having;
    }
    public function setHaving($having) {
        $this->having = $having;
    }    
    public function getAndHaving() {
        return $this->andHaving;
    }
    public function setAndHaving($andHaving) {
        $this->andHaving = $andHaving;
    }    
    public function getGroupBy() {
        return $this->groupBy;
    }
    public function setGroupBy($groupBy) {
        $this->groupBy = $groupBy;
    }    
    public function getAndGroupBy() {
        return $this->andGroupBy;
    }
    public function setAndGroupBy($andGroupBy){
        $this->andGroupBy = $andGroupBy;
    } 
}