const express        = require('express');
const app            = express();
const cors = require('cors');

const port = 8000;
const axios = require('axios');

app.use(cors());

app.post('/test', async (req, res) => {
  try {
    const data = await axios.get('https://en.wikipedia.org/w/api.php?action=query&prop=revisions&rvprop=content&rvsection=0&titles=pizza');
    console.log(data);
    res.send('data');
  } catch(e) {
    console.error(e);
  }
});

app.listen(port, () => {
  console.log('We are live on ' + port);
});
