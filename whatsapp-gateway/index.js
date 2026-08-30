require('dotenv').config();

const express = require('express');
const qrcodeTerminal = require('qrcode-terminal');
const pino = require('pino');
const {
  default: makeWASocket,
  useMultiFileAuthState,
  DisconnectReason,
  fetchLatestBaileysVersion,
} = require('@whiskeysockets/baileys');

const PORT = process.env.PORT || 3001;
const API_KEY = process.env.API_KEY;

if (!API_KEY) {
  console.error('ERROR: isi API_KEY di file .env dulu (lihat .env.example) sebelum menjalankan service ini.');
  process.exit(1);
}

let sock = null;
// connecting | qr | connected | disconnected — dibaca endpoint /status, dipakai
// Laravel buat cek kesiapan sebelum kirim (dan buat tombol "cek status" nanti kalau perlu).
let connectionStatus = 'connecting';

async function startWhatsApp() {
  const { state, saveCreds } = await useMultiFileAuthState('./auth_session');
  const { version } = await fetchLatestBaileysVersion();

  sock = makeWASocket({
    version,
    auth: state,
    logger: pino({ level: 'silent' }),
  });

  sock.ev.on('creds.update', saveCreds);

  sock.ev.on('connection.update', (update) => {
    const { connection, lastDisconnect, qr } = update;

    if (qr) {
      connectionStatus = 'qr';
      console.log('\n=== SCAN QR CODE INI DENGAN WHATSAPP DI NOMOR KHUSUS ANDA ===\n');
      qrcodeTerminal.generate(qr, { small: true });
    }

    if (connection === 'close') {
      connectionStatus = 'disconnected';
      const statusCode = lastDisconnect?.error?.output?.statusCode;
      const shouldReconnect = statusCode !== DisconnectReason.loggedOut;

      console.log(
        'Koneksi WhatsApp terputus.',
        shouldReconnect
          ? 'Mencoba menyambung ulang...'
          : 'Sudah logout dari HP — hapus folder auth_session/ lalu jalankan ulang untuk scan QR baru.'
      );

      if (shouldReconnect) {
        startWhatsApp();
      }
    } else if (connection === 'open') {
      connectionStatus = 'connected';
      console.log('WhatsApp berhasil terhubung. Service siap menerima pengiriman.');
    }
  });
}

startWhatsApp();

const app = express();
app.use(express.json());

function requireApiKey(req, res, next) {
  if (req.header('X-API-Key') !== API_KEY) {
    return res.status(401).json({ success: false, error: 'API key tidak valid.' });
  }
  next();
}

app.get('/status', requireApiKey, (req, res) => {
  res.json({ status: connectionStatus });
});

// Dipanggil dari Laravel (SelfHostedGateway::send()) — TIDAK dimaksudkan diakses
// langsung dari browser/publik, itu kenapa cuma satu endpoint sederhana + API key.
app.post('/send', requireApiKey, async (req, res) => {
  const { phone, message } = req.body || {};

  if (!phone || !message) {
    return res.status(400).json({ success: false, error: 'Field "phone" dan "message" wajib diisi.' });
  }

  if (connectionStatus !== 'connected' || !sock) {
    return res.status(503).json({ success: false, error: 'WhatsApp belum terhubung (belum scan QR atau sesi terputus).' });
  }

  try {
    const jid = `${phone}@s.whatsapp.net`;
    await sock.sendMessage(jid, { text: message });
    return res.json({ success: true });
  } catch (err) {
    return res.status(500).json({ success: false, error: err.message || 'Gagal mengirim pesan.' });
  }
});

app.listen(PORT, () => {
  console.log(`Pabalu WhatsApp Gateway (self-host) jalan di http://127.0.0.1:${PORT}`);
});
