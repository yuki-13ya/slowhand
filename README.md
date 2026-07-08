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

## 既知の技術的負債

- `style.css` が2,600行超の単一ファイルで、ページ／セクション単位の場当たり的な追記が積み重なっている
- ブランドカラーがCSSカスタムプロパティ化されておらず、同系色の16進コードが複数バリエーション直書きされている（例: 青系 `#0048B6` / `#003f91` / `#003f9f`、ピンク系 `#ec4fa5` 系統など）
- → 整理方針・作業ルールは [AGENTS.md](AGENTS.md) を参照

## 参考リンク

- Lightning公式カスタマイズ講座: https://training.vektor-inc.co.jp/courses/lightning-customize/
- Lightning子テーマサンプル: https://github.com/vektor-inc/lightning-child-sample
