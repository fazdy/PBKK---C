using System.Windows;
using StudentManager.ViewModels;

namespace StudentManager;

public partial class MainWindow : Window
{
    public MainWindow()
    {
        InitializeComponent();

        // DataContext menentukan sumber data untuk semua Binding di MainWindow.xaml
        DataContext = new StudentViewModel();
    }
}
