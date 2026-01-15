from typing import Optional, Any, Union

from fastapi import FastAPI, HTTPException
from sentence_transformers import SentenceTransformer

SAFE_CHAR_LIMIT = 1000
# see https://huggingface.co/sentence-transformers/paraphrase-MiniLM-L12-v2
MAX_TOKENS = 384

app = FastAPI()
model = SentenceTransformer('paraphrase-MiniLM-L12-v2')

@app.post("/embed")
async def root(data: dict):
    if "text" in data:
        return embed(data["text"])
    elif "batch" in data:
        return embed_batch(data["batch"])
    else:
        raise HTTPException(status_code=400, detail="text or batch is required")

def embed(text: str) -> dict[str, list]:
    return {"text": text, "embedding": model.encode(text).tolist(), "truncated": is_truncated(text)}

def embed_batch(batch: list, batch_size=32) -> list[dict[str, Union[bool, list, str]]]:
    truncation_status = [is_truncated(text) for text in batch]
    embeddings = model.encode(batch, batch_size=batch_size).tolist()

    return [
        {"text": text, "embedding": embedding, "truncated": truncated}
        for text, embedding, truncated in zip(batch, embeddings, truncation_status)
    ]

def is_truncated(text: str) -> bool:
    char_count = len(text)

    # phase 1: fast heuristic (O(1))
    if char_count < SAFE_CHAR_LIMIT:
        return False

    # phase 2: exact test (O(n)) only if necessary
    token_count = len(model.tokenizer.encode(text))

    return token_count >= MAX_TOKENS
