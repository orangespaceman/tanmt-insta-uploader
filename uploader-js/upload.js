const { IgApiClient } = require('instagram-private-api');
const { appendFile, readFile, writeFile } = require('fs');
const { promisify } = require('util');

const readFileAsync = promisify(readFile);
const appendFileAsync = promisify(appendFile);
const writeFileAsync = promisify(writeFile);
const ig = new IgApiClient();

const cachePath = `${__dirname}/../cache/`;
const lastJsonPath = `${__dirname}/../last.json`;
const nextJsonPath = `${__dirname}/../next.json`;
const logFile = `${__dirname}/../log.txt`;

(async () => {
  const lastData = await getData(lastJsonPath);
  const nextData = await getData(nextJsonPath);
  if (!lastData || !nextData) return;

  if (lastData.last_modified && nextData.last_modified && nextData.last_modified === lastData.last_modified) {
    // log("Image hasn't changed since last upload: " + lastData.title);
    return;
  } else {
    log("Uploading image: " + nextData.title);
  }

  try {
    await login();

    caption = `${nextData.title}\n\n${nextData.tags}`;

    await uploadImage(nextData.imagePath, caption);
    await log(`${nextData.title} - image uploaded`);

    await writeFileAsync(lastJsonPath, JSON.stringify(nextData));

  } catch (e) {
    await log(e.message);
  }
})();

async function login() {
  const config = require(`${__dirname}/config.json`);
  ig.state.generateDevice(config.username);
  // ig.state.proxyUrl = config.proxy;
  await ig.account.login(config.username, config.password);
}

async function getData(file) {
  try {
    const data = await readFileAsync(file);
    if (!data) return;
    return JSON.parse(data);
  } catch (e) {
    return;
  }
}

async function uploadImage(image, caption) {
  const publishResult = await ig.publish.photo({
    file: await readFileAsync(image),
    caption: caption,
  });
  return publishResult;
}

async function log(message) {
  const date = new Date();
  const formattedMessage = `${date.toString()}: ${message}\n`;
  await appendFileAsync(logFile, formattedMessage);
  console.log(formattedMessage);
}