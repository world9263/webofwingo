const FIREBASE_BASE_URL = 'https://berper-default-rtdb.firebaseio.com/';

async function firebaseRequest(path, method = 'GET', data = null) {
    const url = `${FIREBASE_BASE_URL}${path}.json`;
    const options = {
        method: method,
        headers: {
            'Content-Type': 'application/json'
        }
    };
    if (data !== null) {
        options.body = JSON.stringify(data);
    }
    try {
        const response = await fetch(url, options);
        return await response.json();
    } catch (error) {
        console.error("Firebase Request Failed:", error);
        return null;
    }
}
