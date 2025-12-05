const nodemailer = require('nodemailer');

export default async function handler(req, res) {
  if (req.method !== 'POST') {
    return res.status(405).json({ error: 'Method not allowed' });
  }

  try {
    const { first_name, last_name, phone } = req.body;

    // Валидация
    if (!first_name || !last_name || !phone) {
      return res.status(400).json({ error: 'Все поля обязательны для заполнения' });
    }

    // Настройка транспортера для отправки email
    const transporter = nodemailer.createTransport({
      host: 'smtp.yandex.ru',
      port: 465,
      secure: true,
      auth: {
        user: process.env.EMAIL_USER, // Ваш email из env переменных
        pass: process.env.EMAIL_PASSWORD // Пароль приложения
      }
    });

    // Формирование письма
    const mailOptions = {
      from: process.env.EMAIL_USER,
      to: 'lkgromova@yandex.ru',
      subject: 'Новая заявка с сайта Путешествия в Китай',
      text: `
Новая заявка с сайта Путешествия в Китай:

Имя: ${first_name}
Фамилия: ${last_name}
Телефон: ${phone}

Дата отправки: ${new Date().toLocaleString('ru-RU')}
IP адрес: ${req.headers['x-forwarded-for'] || req.socket.remoteAddress}
      `,
      html: `
<h2>Новая заявка с сайта Путешествия в Китай</h2>
<p><strong>Имя:</strong> ${first_name}</p>
<p><strong>Фамилия:</strong> ${last_name}</p>
<p><strong>Телефон:</strong> ${phone}</p>
<p><strong>Дата отправки:</strong> ${new Date().toLocaleString('ru-RU')}</p>
<p><strong>IP адрес:</strong> ${req.headers['x-forwarded-for'] || req.socket.remoteAddress}</p>
      `
    };

    // Отправка email
    await transporter.sendMail(mailOptions);

    return res.status(200).json({ 
      success: true, 
      message: 'Заявка успешно отправлена!' 
    });

  } catch (error) {
    console.error('Ошибка отправки формы:', error);
    return res.status(500).json({ 
      error: 'Произошла ошибка при отправке формы' 
    });
  }
}