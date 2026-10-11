using Avalonia;
using Avalonia.Controls;
using Avalonia.Interactivity;
using Avalonia.Layout;
using Avalonia.Media;
using StudentRegistration.Repositories;

namespace StudentRegistration;

public partial class MainWindow : Window
{
    private readonly StudentRepository _studentRepository;

    public MainWindow()
    {
        InitializeComponent();

        _studentRepository = new StudentRepository();

        // Di Avalonia, dialog hanya bisa tampil setelah jendela utama terbuka,
        // sehingga data dimuat saat event Opened.
        Opened += (_, _) => LoadStudents();
    }

    private void LoadStudents()
    {
        try
        {
            var students = _studentRepository.GetAll();

            StudentDataGrid.ItemsSource = students;

            TotalTextBlock.Text =
                $"Total Mahasiswa: {_studentRepository.GetTotalStudents()}";

            StatusTextBlock.Text =
                $"{students.Count} data dimuat dari SQL Server pukul {DateTime.Now:HH:mm:ss}.";
        }
        catch (Exception ex)
        {
            StatusTextBlock.Text = "Gagal memuat data dari database.";

            ShowMessage(ex.Message, "Database Error");
        }
    }

    private void RefreshButton_Click(
        object? sender,
        RoutedEventArgs e)
    {
        LoadStudents();
    }

    // Avalonia tidak memiliki MessageBox bawaan seperti WPF,
    // sehingga dibuat jendela dialog sederhana.
    private void ShowMessage(string message, string title)
    {
        var okButton = new Button
        {
            Content = "OK",
            Width = 90,
            HorizontalAlignment = HorizontalAlignment.Center,
            HorizontalContentAlignment = HorizontalAlignment.Center
        };

        var dialog = new Window
        {
            Title = title,
            Width = 440,
            SizeToContent = SizeToContent.Height,
            CanResize = false,
            WindowStartupLocation = WindowStartupLocation.CenterOwner,
            Content = new StackPanel
            {
                Margin = new Thickness(20),
                Spacing = 16,
                Children =
                {
                    new TextBlock
                    {
                        Text = message,
                        TextWrapping = TextWrapping.Wrap
                    },
                    okButton
                }
            }
        };

        okButton.Click += (_, _) => dialog.Close();

        _ = dialog.ShowDialog(this);
    }
}
