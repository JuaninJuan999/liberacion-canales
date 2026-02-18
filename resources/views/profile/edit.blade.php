<form action="/profile/update" method="POST">
    @csrf
    <div class="form-group">
        <label for="name">Name:</label>
        <input type="text" class="form-control" id="name" name="name" value="{{ auth()->user()->name }}">
    </div>
    <div class="form-group">
        <label for="email">Email:</label>
        <input type="email" class="form-control" id="email" name="email" value="{{ auth()->user()->email }}">
    </div>
    <button type="submit" class="btn btn-primary">Update Profile</button>
</form>