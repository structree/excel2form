<?php

/**
 * ZipArchiveを拡張して、ディレクトリまるまる扱う機能を追加
 */
class MyZipArchive extends ZipArchive
{
    /**
     * ディレクトリをまるごとzipファイルにします。
     *
     * @param string $dir              ディレクトリパス
     * @param string $inner_path       zipファイル中のディレクトリパス
     * @param bool   $create_empty_dir 空ディレクトリもディレクトリを作成するか
     * @return bool  処理の成否
     */
    public function addDir($dir, $inner_path, $create_empty_dir=false){
        $items = array_diff(scandir($dir), ['.','..']);
        $item_count = count($items);

        if($create_empty_dir || $item_count > 0){
            $this->addEmptyDir($inner_path);
        }

        // 追加するものがないならここで終了する
        if($item_count === 0) return true;


        foreach($items as $_item){ // forで行うなら$itemsは一旦array_values()を通したほうがいい
            $_path = $dir . DIRECTORY_SEPARATOR . $_item;
            $_item_inner_path = $inner_path . DIRECTORY_SEPARATOR . $_item;

            // ディレクトリの場合は再帰的に処理する
            if(is_dir($_path)){
                $_r = call_user_func( // "$this->addDir"より保守的に好ましい
                    [$this, __FUNCTION__], $_path, $_item_inner_path);
                if(!$_r) return false;
            }
            // ファイルの場合でかつ処理に失敗したときはこちら
            else if(!$this->addFile($_path, $_item_inner_path)
                && !$this->on_recursive_error($dir, $inner_path, $create_empty_dir)){
                return false;
            }
            // ファイルの追加成功時は何も他には行わない
        }

        return true;
    }

}
