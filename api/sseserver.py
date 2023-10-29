from flask import Flask, render_template, Response

app = Flask(__name__)

def event_stream():
    for i in range(10):
        yield f"data: {i}\n\n"  # Send data to the client

@app.route('/stream')
def stream():
    return Response(event_stream(), content_type='text/event-stream')

@app.route('/')
def index():
    return render_template('index.html')

if __name__ == '__main__':
    app.run(debug=True)

