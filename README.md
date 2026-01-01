# 🧘 Yoga Studio ERP

**Yoga Studio ERP** è un software **ERP web-based open source** per la gestione di **centri yoga, scuole olistiche e studi di discipline affini**.

Il progetto nasce per offrire uno strumento **libero, auto-ospitabile e personalizzabile**, costruito sulle reali esigenze organizzative di un centro yoga, senza dipendere da piattaforme proprietarie o modelli ad abbonamento.

> Versione corrente: **beta_1.0.0**  
> Stato: **beta – in sviluppo attivo**

---

## 📌 A chi è rivolto

- Centri yoga e scuole olistiche
- Insegnanti indipendenti
- Associazioni sportive e culturali
- Sviluppatori interessati a un ERP verticale open source

---

## 🎯 Visione del progetto

Molti centri yoga utilizzano strumenti frammentati:
- fogli Excel
- messaggi WhatsApp
- software generici poco flessibili

Yoga Studio ERP vuole diventare una **base gestionale solida e coerente con i valori dello yoga**:
- semplicità
- consapevolezza
- sostenibilità
- autonomia tecnologica

---

## ✨ Funzionalità disponibili (beta_1.0.0)

### 👤 Gestione utenti
- Autenticazione utenti
- Sistema di ruoli
- Accesso protetto al backend gestionale

### 🧘 Gestione allievi
- Anagrafica studenti
- Gestione dei dati principali dell’allievo
- Struttura pronta per storico attività e presenze

### 📅 Gestione corsi
- Creazione e modifica dei corsi
- Associazione corsi ↔ insegnanti
- Base dati per pianificazione lezioni

### 🏫 Struttura del centro
- Modellazione delle entità principali del centro yoga
- Architettura predisposta per:
  - sale
  - discipline
  - livelli

### ⚙️ Architettura tecnica
- Backend basato su **Laravel**
- Database relazionale **MySQL**
- Frontend compilato con **Vite**
- Codice modulare ed estendibile

> ⚠️ La versione beta ha funzionalità incomplete e UI in evoluzione.

---

## 👀 Preview delle funzionalità


::contentReference[oaicite:0]{index=0}


La versione beta permette già di:
- accedere a un pannello gestionale protetto
- gestire utenti e ruoli
- creare e amministrare allievi e corsi
- impostare la struttura di base del centro

Le schermate e i flussi sono progettati per essere **semplici e focalizzati sulla pratica quotidiana**, non sulla complessità amministrativa.

---

## 🚧 Roadmap (sviluppo futuro)

- Gestione abbonamenti e pacchetti
- Presenze e storico lezioni
- Calendario avanzato
- Reportistica di base
- Miglioramento UX/UI
- Internazionalizzazione (i18n)
- API per integrazioni esterne

---

## 🛠️ Requisiti software

- PHP **8.4.x**
- Database **MySQL**
- Composer *(solo ambiente di sviluppo)*
- Node.js *(solo per build frontend in sviluppo)*

---

## 🚀 Installazione (ambiente di sviluppo)

```bash
composer install
php artisan migrate

# dalla root del progetto Laravel
npm install
npm run build
php artisan view:clear
php artisan view:cache
php artisan optimize:clear
php artisan route:clear
php artisan serve --host=127.0.0.1 --port=8000

## 🚀 Avvio Locale (ambiente di sviluppo)
php artisan view:clear
php artisan view:cache
php artisan optimize:clear
php artisan route:clear
php artisan serve --host=127.0.0.1 --port=8000

🌐 Deploy su hosting (senza Node.js)

Caricare i file PHP (app/, routes/, ecc.)
Caricare la cartella public/ (inclusa public/build)
Caricare la cartella vendor/ (se Composer non è disponibile)
Configurare il file .env

Eseguire:
```bash
php artisan config:cache
php artisan route:cache

⚙️ Personalizzazioni e consulenze
🤝 Contributi

Contributi, segnalazioni di bug e proposte di miglioramento sono benvenuti.
Il progetto cresce grazie alla community.

📜 Licenza

Questo progetto è rilasciato sotto licenza:

GNU AGPL-3.0

Chiunque utilizzi il software, anche tramite rete (SaaS),
è tenuto a rendere pubbliche eventuali modifiche al codice sorgente.

🌱 Filosofia

Software libero per una pratica consapevole.
Tecnologia al servizio delle persone, non il contrario.


---

### 🔧 Prossimi miglioramenti possibili
Se vuoi, nel prossimo messaggio posso:
- adattare il README **esattamente alle entità del database**
- preparare una **sezione “Demo / Screenshot reali”**
- creare una **ROADMAP.md tecnica**
- scrivere un **CONTRIBUTING.md**

---

💙 Sostieni il progetto – Donate Now

Yoga Studio ERP è un progetto open source e gratuito, sviluppato e mantenuto con tempo, cura e competenze professionali.

Se questo software ti è utile, puoi sostenere lo sviluppo con una donazione libera:

👉Dona ora con PayPal
https://paypal.me/TUONOME

Le donazioni contribuiscono a:

sviluppo di nuove funzionalità

manutenzione e miglioramento del codice

documentazione e supporto alla community

Ogni contributo, anche piccolo, aiuta a mantenere il progetto vivo e sostenibile 🌱
