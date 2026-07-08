# SLOWHAND ウェブサイト

WordPress で構築された SLOWHAND（および併設ブランド「ゆめみ堂」）の公式サイトのソースリポジトリです。

## 概要

- 本番サイト: `happy2calf.com`（XServer上で運用）
- CMS: WordPress（core 7.0系）
- テーマ: [Lightning](https://lightning.vektor-inc.co.jp/)（VEKTOR社製, v15.36.0）＋ 自作の子テーマ `lightning-child`
- サイト独自のカスタマイズ（レイアウト・デザイン・独自ショートコード等）は **すべて子テーマ `wp-content/themes/lightning-child` の中に閉じています**

## このリポジトリの管理範囲

このリポジトリでは **`wp-content/themes/lightning-child` と、このREADME・AGENTS.md などのドキュメントのみ** をgit管理します。

以下は公式配布物であり、自分たちでは編集しないため管理対象外です（`.gitignore`で除外）:

- WordPressコア本体（`wp-admin/`, `wp-includes/`, ルート直下の `wp-*.php` など）
- 親テーマ `lightning`
- プラグイン一式（`wp-content/plugins/`）
- アップロード画像等（`wp-content/uploads/`）
- `wp-content/*-old*` 系のフォルダ（過去のバックアップ/移行の残骸。現状ほぼ空で、このリポジトリの対象外）

これにより、差分が「自分たちが実際に書いたコード」だけになり、全体を俯瞰しやすい状態を保ちます。

## ディレクトリ構成（子テーマ）

```
wp-content/themes/lightning-child/
├── style.css        # サイト独自CSSの本体（現状はここに直接記述。SCSSパイプラインは未使用）
├── functions.php     # 独自ショートコード（TOP NEWS等）、フッターメニューWalkerなど
├── screenshot.png
├── assets/
│   ├── css/           # サンプルの空ファイル。functions.php 側で読み込みは無効化されている
│   └── _scss/          # 同上。現状本番には反映されていない未使用の雛形
└── tests/              # PHPUnit用の雛形（Vektor社サンプル由来）
```

**注意**: `assets/_scss` によるSassビルド環境が用意されていますが、`functions.php` 内の `$my_lightning_additional_css` が `false` のため無効化されており、実際のスタイルはテーマ直下の `style.css` に直接書かれたものが使われています。

## ローカルでの作業

[Local](https://localwp.com/)（Local by Flywheel）を使ってローカルにWordPress実行環境を用意します。

### 初回セットアップ

1. Localで新規サイトを作成する（PHPバージョンは本番のXServer環境に合わせる。WordPressコアは本番と同じ7.0系を使う）
2. 本番のDB・アップロード画像を同期する
   - 本番には `UpdraftPlus` プラグインが導入済みなので、wp-admin からバックアップを取得し、Localの該当サイトへリストアするのが手早い
   - （UpdraftPlusのバックアップには子テーマ自体も含まれるが、コードの実体は次の手順でこのリポジトリに置き換える）
3. テーマをこのリポジトリに一本化する
   - Localサイトの `wp-content/themes/lightning-child` を削除し、代わりにこのリポジトリの `wp-content/themes/lightning-child` をシンボリックリンクで配置する（コピー運用にすると差分がリポジトリと乖離するため避ける）
   - Windows（管理者権限のコマンドプロンプト）での例:
     ```
     mklink /D "C:\Users\<user>\Local Sites\<site-name>\app\public\wp-content\themes\lightning-child" "D:\website\slowhand\slowhand\wp-content\themes\lightning-child"
     ```
4. wp-admin の「外観 > テーマ」で `Lightning Child` が有効になっていることを確認する

### 通常の作業フロー

1. このリポジトリの `wp-content/themes/lightning-child` を編集する（シンボリックリンク経由でLocalサイトにも即反映される）
2. LocalでサイトのURLを開き、目視で確認する
3. 問題なければコミットし、README「本番反映」の手順でFTP/SFTPアップロードする

## 本番反映（デプロイ）

FTP/SFTPで手動アップロードしています。

1. ローカルで `wp-content/themes/lightning-child` 配下を編集
2. 変更内容を確認（可能であればステージング環境やローカルプレビューで見た目を確認）
3. FTP/SFTPクライアントで変更したファイルのみ本番 (`happy2calf.com`) へアップロード

本番に直接反映される運用のため、変更は小さく確認しながら進めます。

## 配色について

ブランドカラーは `style.css` 冒頭の `:root` にCSSカスタムプロパティとしてまとめてあります。**色を変更する場合はここだけ書き換えれば全体に反映されます。**

- `--color-primary` 系: SLOWHAND（ミッドナイトブルー）
- `--color-accent` 系: ゆめみ堂（ラベンダー）
- `--color-bg-base` / `--color-text` / `--color-border`: 共通の背景・本文文字・ボーダー色

### 注意: 色の設定はもう1箇所ある（DB側）

上記はgit管理下の`style.css`だけの話です。**それとは別に、WordPressのデータベース側にも色設定が存在します。**

Lightningテーマの「VK Color Manager」機能により、ブロックエディタの色スウォッチ「カスタム1」「カスタム2」に色が登録されています（`外観 > カスタマイズ > 色` の一番下）。過去にページ本文の見出し等でこのスウォッチを直接選んで色付けした箇所があり、そこは`style.css`をいくら直しても変わりません。

ブランドカラーを変更する際は、`style.css`の`:root`に加えて、**この「カスタム1」「カスタム2」も同じ値に更新する**必要があります。この設定はgit管理外（DBの中）なので、Local環境と本番環境それぞれで個別に変更してください。

- カスタム1 = `--color-primary` と同じ値
- カスタム2 = `--color-accent` と同じ値

## 既知の技術的負債

- `style.css` が2,600行超の単一ファイル。以前はページ／セクション単位の場当たり的なカラー直書きが積み重なっていたが、2026-07にCSSカスタムプロパティへ整理済み（詳細はgit履歴を参照）
- → 作業ルールは [AGENTS.md](AGENTS.md) を参照

## 参考リンク

- Lightning公式カスタマイズ講座: https://training.vektor-inc.co.jp/courses/lightning-customize/
- Lightning子テーマサンプル: https://github.com/vektor-inc/lightning-child-sample
