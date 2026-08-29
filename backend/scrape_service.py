from fastapi import FastAPI, HTTPException
import libtorrent as lt
import time

app = FastAPI()
ses = lt.session()

@app.get("/scrape")
def scrape(hash: str):
    try:
        info = lt.sha1_hash(bytes.fromhex(hash))
        h = ses.add_torrent({"info_hash": info})
        time.sleep(2)
        s = h.status()
        return {
            "seeders": s.num_seeds,
            "leechers": s.num_peers,
            "completed": s.num_complete
        }
    except Exception as e:
        raise HTTPException(status_code=500, detail=str(e))
