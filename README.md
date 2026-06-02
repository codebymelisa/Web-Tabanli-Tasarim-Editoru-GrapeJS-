# Web Tabanlı Görsel Sayfa Tasarım Editörü (GrapesJS Studio)

Ege Üniversitesi - Görsel Programlama III Ödevi

## Proje Hakkında
Bu proje, kullanıcıların sürükle-bırak yöntemiyle web sayfaları tasarlamasını sağlayan ve üretilen HTML/CSS kod bloklarını anlık olarak MySQL veritabanına JSON formatında yedekleyen web tabanlı bir içerik yönetim aracıdır.

## Kullanılan Teknolojiler
- GrapesJS Framework
- PHP 8.2 (Apache)
- MySQL 8.0
- Docker & Docker Compose

## Docker Üzerinde Çalıştırma Talimatı

Projeyi yerel ortamınızda test etmek için aşağıdaki adımları sırayla takip edebilirsiniz:

1. Bu depoyu (repository) bilgisayarınıza indirin veya klonlayın.
2. Terminali proje kök dizininde açın.
3. Aşağıdaki komutu çalıştırarak sunucu ve veritabanı altyapısını ayağa kaldırın:

```bash
docker-compose up --build -d
