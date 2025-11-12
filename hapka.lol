server {
    listen 80;
    listen [::]:80;
    server_name hapka.lol www.hapka.lol;
    
    if ($host = www.hapka.lol) {
        return 301 http://hapka.lol$request_uri;
    }
    
    root /var/www/hapka.lol;
    index index.php index.html;
    
    access_log /var/log/nginx/hapka.lol.access.log;
    error_log /var/log/nginx/hapka.lol.error.log;
    
    client_max_body_size 100M;
    client_body_buffer_size 128k;
    
    proxy_connect_timeout 600;
    proxy_send_timeout 600;
    proxy_read_timeout 600;
    send_timeout 600;
    
    limit_rate_after 10m;
    
    server_tokens off;
    
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header Referrer-Policy "strict-origin-when-cross-origin" always;
    
    location ~* \.(jpg|jpeg|png|gif|ico|css|js|svg|woff|woff2|ttf|eot)$ {
        expires 30d;
        add_header Cache-Control "public, immutable";
        access_log off;
    }
    
    location ~ /\. {
        deny all;
        access_log off;
        log_not_found off;
    }
    
    location ~ /\.(env|htaccess|htpasswd|ini|log|sh|sql|conf)$ {
        deny all;
        access_log off;
        log_not_found off;
    }
    
    location /api/ {
        try_files $uri $uri/ /api/1/upload.php;
        
        location ~ \.php$ {
            include snippets/fastcgi-php.conf;
            fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
            fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
            fastcgi_read_timeout 600;
        }
    }
    
    location ~ "^/[A-Za-z][A-Za-z][A-Za-z][A-Za-z][A-Za-z]$" {
        rewrite "^/([A-Za-z]+)$" /templates/view.php?code=$1 last;
    }
    location ~ "^/[A-Za-z][A-Za-z][A-Za-z][A-Za-z][A-Za-z][A-Za-z]$" {
        rewrite "^/([A-Za-z]+)$" /templates/view.php?code=$1 last;
    }
    
    location = /admin.php {
        rewrite ^ /templates/admin.php last;
    }
    
    location ~ ^/ShareX(?:\.php)?$ {
        rewrite ^ /templates/ShareX.php last;
    }
    
    location ~ ^/uploads/.*\.php$ {
        deny all;
        access_log off;
        log_not_found off;
    }
    
    location ~* ^/uploads/.*\.(jpg|jpeg|png|gif|webp|avif)$ {
        expires 1h;
        add_header Cache-Control "public";
        access_log off;
    }
    
    location /uploads/ {
        deny all;
        access_log off;
        log_not_found off;
    }
    
    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        fastcgi_read_timeout 600;
        fastcgi_buffer_size 128k;
        fastcgi_buffers 4 256k;
        fastcgi_busy_buffers_size 256k;
    }
    
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
    
    location = /robots.txt {
        allow all;
        log_not_found off;
        access_log off;
    }
    
    location = /favicon.ico {
        log_not_found off;
        access_log off;
    }

    location ~ /.well-known/acme-challenge {
        allow all;
        root /var/www/html;
    }
}